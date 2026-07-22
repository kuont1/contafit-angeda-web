## Descripción del cambio

[Describe brevemente qué hace este PR]

## Tipo de cambio

- [ ] Cambio menor (visual, texto, configuración sin lógica)
- [ ] Cambio mayor (nueva funcionalidad, corrección de lógica, requerimiento del SRS)

## Requerimiento relacionado

RF/RNF: [ej. RF-04] — o si es cambio menor solicitado por el cliente, indicar el origen de la solicitud

## Checklist de verificación (Anexo A - Política de Gestión de Cambios)

### Para cambio menor
- [ ] El cambio se visualiza correctamente en la interfaz
- [ ] No se modificó lógica de backend ni estructura de datos
- [ ] No rompe ninguna funcionalidad existente

### Para cambio mayor
- [ ] El código ejecuta sin errores
- [ ] Cumple el criterio de aceptación del requerimiento definido en el SRS
- [ ] Las pruebas (manuales o automatizadas) pasan correctamente
- [ ] No revierte funcionalidad previamente aceptada
- [ ] El commit referencia el RF/RNF correspondiente

## Notas de revisión

[Comentario final del revisor antes de aprobar o rechazar, ej. "Verificado contra RF-04, pruebas manuales correctas, se aprueba"]
