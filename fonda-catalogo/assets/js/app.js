// app.js - Catálogo Fonda Juanita

document.addEventListener('DOMContentLoaded', () => {
    const catalogo = new CatalogoProductos();
    window.cerrarModal = () => catalogo.cerrarModal(); // Para compatibilidad con el botón cancelar
});

class CatalogoProductos {
    constructor() {
        this.productos = [];
        this.modal = document.getElementById('modalProducto');
        this.form = document.getElementById('formProducto');
        this.init();
    }

    init() {
        this.bindEvents();
        this.cargarProductos();
    }

    bindEvents() {
        document.getElementById('btnAgregar').addEventListener('click', () => this.abrirModal());
        document.querySelector('.close').addEventListener('click', () => this.cerrarModal());
        window.addEventListener('click', (e) => {
            if (e.target === this.modal) this.cerrarModal();
        });
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.guardarProducto();
        });
        document.getElementById('imagen').addEventListener('change', (e) => this.previewImagen(e.target.files[0]));
    }

    async cargarProductos() {
        try {
            const response = await fetch('api/productos.php');
            const result = await response.json();
            this.productos = Array.isArray(result) ? result : (result.data || []);
            this.renderizarCatalogo();
        } catch (error) {
            this.mostrarError('Error al cargar productos: ' + error.message);
        }
    }

    renderizarCatalogo() {
        const container = document.getElementById('catalogoProductos');
        if (!this.productos.length) {
            container.innerHTML = `<div class="loading"><p>No hay productos disponibles. ¡Agrega el primer producto!</p></div>`;
            return;
        }
        container.innerHTML = this.productos.map(producto => `
            <div class="producto-card">
                <div class="producto-imagen">
                    ${producto.imagen ? `<img src="uploads/${producto.imagen}" alt="${producto.nombre}" style="width: 100%; height: 100%; object-fit: cover;">` : '🍽️'}
                </div>
                <div class="producto-nombre">${producto.nombre}</div>
                <div class="producto-precio">$${this.formatearPrecio(producto.precio)}</div>
                <div class="producto-actions">
                    <button class="btn-edit" onclick="catalogo.editarProducto(${producto.id})">✏️ Editar</button>
                    <button class="btn-delete" onclick="catalogo.eliminarProducto(${producto.id})">🗑️ Eliminar</button>
                </div>
            </div>
        `).join('');
    }

    abrirModal(producto = null) {
        document.getElementById('modalTitle').textContent = producto ? 'Editar Producto' : 'Agregar Producto';
        if (producto) {
            document.getElementById('productoId').value = producto.id;
            document.getElementById('nombre').value = producto.nombre;
            document.getElementById('precio').value = producto.precio;
            document.getElementById('imagenPreview').innerHTML = producto.imagen ? `<img src="uploads/${producto.imagen}" alt="Preview">` : '';
        } else {
            this.form.reset();
            document.getElementById('productoId').value = '';
            document.getElementById('imagenPreview').innerHTML = '';
        }
        this.modal.style.display = 'block';
    }

    cerrarModal() {
        this.modal.style.display = 'none';
        this.form.reset();
        document.getElementById('imagenPreview').innerHTML = '';
    }

    async guardarProducto() {
        const formData = new FormData(this.form);
        const productoId = document.getElementById('productoId').value;
        try {
            let nombreImagen = null;
            const imagenFile = document.getElementById('imagen').files[0];
            if (imagenFile) {
                nombreImagen = await this.subirImagen(imagenFile);
            }
            const data = {
                action: productoId ? 'update' : 'create',
                nombre: formData.get('nombre'),
                precio: parseFloat(formData.get('precio'))
            };
            if (productoId) data.id = parseInt(productoId);
            if (nombreImagen) data.imagen = nombreImagen;
            const response = await fetch('api/productos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success || typeof result === 'number') {
                this.mostrarExito('Producto guardado correctamente');
                this.cerrarModal();
                this.cargarProductos();
            } else {
                this.mostrarError(result.message || 'Error al guardar producto');
            }
        } catch (error) {
            this.mostrarError('Error al guardar: ' + error.message);
        }
    }

    async subirImagen(file) {
        const formData = new FormData();
        formData.append('imagen', file);
        const response = await fetch('api/upload.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            return result.filename;
        } else {
            throw new Error(result.message);
        }
    }

    editarProducto(id) {
        const producto = this.productos.find(p => p.id === id);
        if (producto) this.abrirModal(producto);
    }

    async eliminarProducto(id) {
        if (!confirm('¿Estás seguro de que quieres eliminar este producto?')) return;
        try {
            const response = await fetch('api/productos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id })
            });
            const result = await response.json();
            if (result.success) {
                this.mostrarExito('Producto eliminado');
                this.cargarProductos();
            } else {
                this.mostrarError(result.message || 'Error al eliminar producto');
            }
        } catch (error) {
            this.mostrarError('Error al eliminar: ' + error.message);
        }
    }

    previewImagen(file) {
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('imagenPreview').innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            };
            reader.readAsDataURL(file);
        }
    }

    formatearPrecio(precio) {
        return new Intl.NumberFormat('es-CL').format(precio);
    }

    mostrarExito(mensaje) {
        alert('✅ ' + mensaje);
    }

    mostrarError(mensaje) {
        alert('❌ ' + mensaje);
    }
}
