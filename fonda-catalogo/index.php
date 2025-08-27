<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fonda Juanita - Catálogo de Productos</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <div class="header">
        FONDA JUANITA - Catálogo de Productos
    </div>
    <button id="btnAgregar" class="add-product-btn">+ Agregar Producto</button>
    <div class="catalogo-container">
        <div id="catalogoProductos" class="product-grid">
            <!-- Los productos se cargarán aquí dinámicamente -->
        </div>
    </div>

    <!-- Modal para agregar/editar productos -->
    <div id="modalProducto" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Agregar Producto</h2>
            <form id="formProducto" enctype="multipart/form-data">
                <input type="hidden" id="productoId">
                <div class="form-group">
                    <label for="nombre">Nombre del Producto:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="precio">Precio ($):</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="imagen">Imagen:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                    <div id="imagenPreview"></div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Guardar</button>
                    <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>

</html>