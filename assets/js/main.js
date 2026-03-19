// Búsqueda de productos en tiempo real e inicialización general
document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar Toggle (Mobile)
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (menuToggle && sidebar && overlay) {
        menuToggle.onclick = function (e) {
            e.preventDefault();
            e.stopPropagation();
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        };

        overlay.onclick = function () {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        };
    }

    // 2. Búsquedas en tiempo real
    const searchProducto = document.getElementById('search-producto');
    if (searchProducto) {
        searchProducto.addEventListener('keyup', function () {
            const termino = this.value;
            if (termino.length >= 2) {
                buscarProductos(termino);
            }
        });
    }

    const searchCliente = document.getElementById('search-cliente');
    if (searchCliente) {
        searchCliente.addEventListener('keyup', function () {
            const termino = this.value;
            if (termino.length >= 2) {
                buscarClientes(termino);
            }
        });
    }

    // 3. Validaciones y Cálculos (Ventas/Reparaciones)
    const inputCantidad = document.getElementById('cantidad');
    if (inputCantidad) {
        inputCantidad.addEventListener('input', function () {
            const stockElement = document.getElementById('stock_disponible');
            if (stockElement) {
                const stock = parseInt(stockElement.value);
                const cantidad = parseInt(this.value);
                if (cantidad > stock) {
                    alert(`Solo hay ${stock} unidades disponibles en stock`);
                    this.value = stock;
                }
            }
            if (typeof calcularSubtotal === 'function') calcularSubtotal();
        });
    }

    const tipoReporte = document.getElementById('tipo_reporte');
    if (tipoReporte) {
        tipoReporte.addEventListener('change', function () {
            const periodo = document.getElementById('periodo_reporte');
            if (periodo) {
                periodo.style.display = (this.value === 'personalizado') ? 'block' : 'none';
            }
        });
    }

    const formVenta = document.getElementById('form-venta');
    if (formVenta) {
        formVenta.addEventListener('submit', function (e) {
            // productosVenta es una variable global definida abajo
            if (typeof productosVenta !== 'undefined' && productosVenta.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un producto a la venta');
                return;
            }
            if (typeof productosVenta !== 'undefined') {
                const inputProductos = document.createElement('input');
                inputProductos.type = 'hidden';
                inputProductos.name = 'productos';
                inputProductos.value = JSON.stringify(productosVenta);
                this.appendChild(inputProductos);
            }
        });
    }

    // 4. Toggle password visibility (OJO) - Event Delegation para 100% confiabilidad
    document.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('.toggle-password');
        if (!toggleBtn) return;

        const container = toggleBtn.closest('.password-field') || toggleBtn.parentElement;
        const input = container.querySelector('input');
        const icon = toggleBtn.querySelector('i');

        if (input && icon) {
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    });
});

// --- Funciones Globales ---

function buscarProductos(termino) {
    fetch(`buscar_productos.php?q=${termino}`)
        .then(response => response.json())
        .then(data => {
            mostrarSugerencias(data, 'producto');
        });
}

function buscarClientes(termino) {
    fetch(`buscar_clientes.php?q=${termino}`)
        .then(response => response.json())
        .then(data => {
            mostrarSugerencias(data, 'cliente');
        });
}

function mostrarSugerencias(items, tipo) {
    const suggestionsDiv = document.getElementById(`suggestions-${tipo}`);
    if (!suggestionsDiv) return;

    if (items.length > 0) {
        let html = '';
        items.forEach(item => {
            if (tipo === 'producto') {
                html += `
                    <div class="suggestion-item" onclick="seleccionarProducto(${item.id}, '${item.nombre}', ${item.precio}, ${item.stock})">
                        ${item.nombre} - Stock: ${item.stock} 
                        <span class="price">$${item.precio}</span>
                    </div>
                `;
            } else {
                html += `
                    <div class="suggestion-item" onclick="seleccionarCliente(${item.id}, '${item.nombres} ${item.apellidos}', '${item.telefono}', '${item.direccion}')">
                        ${item.nombres} ${item.apellidos} - ${item.telefono}
                    </div>
                `;
            }
        });
        suggestionsDiv.innerHTML = html;
        suggestionsDiv.classList.add('show');
    } else {
        suggestionsDiv.classList.remove('show');
    }
}

function seleccionarProducto(id, nombre, precio, stock) {
    const pId = document.getElementById('producto_id');
    const pNombre = document.getElementById('nombre_producto');
    const pPrecio = document.getElementById('precio');
    const pStock = document.getElementById('stock_disponible');
    const pCant = document.getElementById('cantidad');

    if (pId) pId.value = id;
    if (pNombre) pNombre.value = nombre;
    if (pPrecio) pPrecio.value = precio;
    if (pStock) pStock.value = stock;
    if (pCant) pCant.max = stock;

    const suggestionBox = document.getElementById('suggestions-producto');
    if (suggestionBox) suggestionBox.classList.remove('show');

    calcularSubtotal();
}

function seleccionarCliente(id, nombreCompleto, telefono, direccion) {
    const cId = document.getElementById('cliente_id');
    const cNombre = document.getElementById('nombre_cliente');
    const cTelf = document.getElementById('telefono');
    const cDir = document.getElementById('direccion');

    if (cId) cId.value = id;
    if (cNombre) cNombre.value = nombreCompleto;
    if (cTelf) cTelf.value = telefono;
    if (cDir) cDir.value = direccion;

    const suggestionBox = document.getElementById('suggestions-cliente');
    if (suggestionBox) suggestionBox.classList.remove('show');
}

function calcularSubtotal() {
    const inputCant = document.getElementById('cantidad');
    const inputPrec = document.getElementById('precio');
    const inputSub = document.getElementById('subtotal');
    const inputTotal = document.getElementById('total');

    if (inputCant && inputPrec && inputSub && inputTotal) {
        const cantidad = parseFloat(inputCant.value) || 0;
        const precio = parseFloat(inputPrec.value) || 0;
        const subtotal = cantidad * precio;
        inputSub.value = `$${subtotal.toFixed(2)}`;
        inputTotal.value = subtotal.toFixed(2);
    }
}

function imprimirSticker(contenido) {
    const ventana = window.open('', '_blank');
    ventana.document.write(contenido);
    ventana.document.close();
    ventana.focus();
    ventana.print();
}

function compartirWhatsApp(mensaje) {
    const url = `https://wa.me/?text=${encodeURIComponent(mensaje)}`;
    window.open(url, '_blank');
}

function confirmarEliminar(mensaje) {
    return confirm(mensaje || '¿Estás seguro de eliminar este registro?');
}

// --- Gestión de Ventas (Variable Global) ---
let productosVenta = [];

function agregarProductoVenta() {
    const pId = document.getElementById('producto_id');
    const pNombre = document.getElementById('nombre_producto');
    const pCant = document.getElementById('cantidad');
    const pPrecio = document.getElementById('precio');

    if (!pId || !pId.value || !pNombre.value || !pCant.value || !pPrecio.value) {
        alert('Por favor complete todos los campos del producto');
        return;
    }

    const cantidad = parseInt(pCant.value);
    const precio = parseFloat(pPrecio.value);
    const subtotal = cantidad * precio;

    const producto = {
        id: pId.value,
        nombre: pNombre.value,
        cantidad: cantidad,
        precio: precio,
        subtotal: subtotal
    };

    productosVenta.push(producto);
    actualizarTablaVenta();
    limpiarCamposProducto();
}

function actualizarTablaVenta() {
    const tbody = document.getElementById('productos-venta');
    const inputTotalVenta = document.getElementById('total-venta');
    if (!tbody) return;

    let html = '';
    let total = 0;

    productosVenta.forEach((producto, index) => {
        total += producto.subtotal;
        html += `
            <tr>
                <td>${producto.nombre}</td>
                <td>${producto.cantidad}</td>
                <td>$${producto.precio.toFixed(2)}</td>
                <td>$${producto.subtotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn-small btn-danger" onclick="eliminarProductoVenta(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    if (inputTotalVenta) inputTotalVenta.value = total.toFixed(2);
}

function eliminarProductoVenta(index) {
    productosVenta.splice(index, 1);
    actualizarTablaVenta();
}

function limpiarCamposProducto() {
    const fields = ['producto_id', 'nombre_producto', 'cantidad', 'precio', 'stock_disponible', 'subtotal'];
    fields.forEach(f => {
        const el = document.getElementById(f);
        if (el) el.value = '';
    });
}
