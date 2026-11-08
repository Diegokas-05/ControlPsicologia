# Flujo del Proyecto: Control de Psicología

* **Entidad Operativa:** Cita / Expediente Psicológico[cite: 1, 2].
* **Operador autorizado:** `admin`[cite: 1, 6].
* **Definición de estados:**
  * `iniciar`: El psicólogo inicia la atención de la consulta médica/psicológica (`pendiente` -> `en_proceso`).
  * `finalizar`: Se concluye la sesión o evaluación clínica (`en_proceso` -> `finalizado`)[cite: 2].