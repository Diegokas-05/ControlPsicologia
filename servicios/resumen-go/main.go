package main

import (
	"crypto/hmac"
	"encoding/json"
	"errors"
	"io"
	"log"
	"math"
	"mime"
	"net/http"
	"os"
	"strings"
	"time"
	"unicode/utf8"
)

type Grupo struct {
	Etiqueta string `json:"etiqueta"`
	Cantidad *int   `json:"cantidad"`
}

type Fila struct {
	Etiqueta   string  `json:"etiqueta"`
	Cantidad   int     `json:"cantidad"`
	Porcentaje float64 `json:"porcentaje"`
}

type Respuesta struct {
	Total  int    `json:"total"`
	Grupos []Fila `json:"grupos"`
}

func calcular(grupos []Grupo) (Respuesta, error) {
	salida := Respuesta{Grupos: []Fila{}}
	if len(grupos) < 1 || len(grupos) > 20 {
		return salida, errors.New("se requieren entre 1 y 20 grupos")
	}

	vistos := map[string]bool{}
	for _, g := range grupos {
		nombre := strings.TrimSpace(g.Etiqueta)
		clave := strings.ToLower(nombre)

		if nombre == "" || utf8.RuneCountInString(nombre) > 40 ||
			vistos[clave] || g.Cantidad == nil ||
			*g.Cantidad < 0 || *g.Cantidad > 1000000 {
			return salida, errors.New("grupo no valido")
		}
		vistos[clave] = true
		salida.Total += *g.Cantidad
		salida.Grupos = append(salida.Grupos, Fila{
			Etiqueta: nombre, Cantidad: *g.Cantidad,
		})
	}

	for i := range salida.Grupos {
		if salida.Total > 0 {
			n := float64(salida.Grupos[i].Cantidad)
			salida.Grupos[i].Porcentaje = math.Round(n/float64(salida.Total)*10000) / 100
		}
	}
	return salida, nil
}

func responder(w http.ResponseWriter, estado int, datos any) {
	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	w.Header().Set("Cache-Control", "no-store")
	w.WriteHeader(estado)
	_ = json.NewEncoder(w).Encode(datos)
}

func rutas(clave string) http.Handler {
	mux := http.NewServeMux()

	mux.HandleFunc("GET /salud", func(w http.ResponseWriter, r *http.Request) {
		responder(w, 200, map[string]string{"estado": "ok"})
	})

	mux.HandleFunc("POST /resumen", func(w http.ResponseWriter, r *http.Request) {
		recibida := r.Header.Get("X-Service-Key")
		if !hmac.Equal([]byte(recibida), []byte(clave)) {
			responder(w, 401, map[string]string{"error": "acceso no valido"})
			return
		}

		tipo, _, err := mime.ParseMediaType(r.Header.Get("Content-Type"))
		if err != nil || tipo != "application/json" {
			responder(w, 415, map[string]string{"error": "se requiere JSON"})
			return
		}

		var entrada struct {
			Grupos []Grupo `json:"grupos"`
		}

		r.Body = http.MaxBytesReader(w, r.Body, 8192)
		dec := json.NewDecoder(r.Body)
		dec.DisallowUnknownFields()

		var extra any
		if dec.Decode(&entrada) != nil || dec.Decode(&extra) != io.EOF {
			responder(w, 400, map[string]string{"error": "JSON no valido"})
			return
		}

		resultado, err := calcular(entrada.Grupos)
		if err != nil {
			responder(w, 422, map[string]string{"error": "grupos no validos"})
			return
		}
		responder(w, 200, resultado)
	})
	return mux
}

func main() {
	clave := strings.TrimSpace(os.Getenv("GO_SERVICE_KEY"))
	if len(clave) < 32 {
		log.Fatal("Configure GO_SERVICE_KEY con la clave local")
	}

	servidor := &http.Server{
		Addr: "127.0.0.1:8081", Handler: rutas(clave),
		ReadHeaderTimeout: 2 * time.Second,
		ReadTimeout:       3 * time.Second,
		WriteTimeout:      3 * time.Second,
		IdleTimeout:       30 * time.Second,
	}

	log.Print("Servicio de resumen en http://127.0.0.1:8081")
	log.Fatal(servidor.ListenAndServe())
}
