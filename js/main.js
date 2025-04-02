document.addEventListener('DOMContentLoaded', function() {
    // Agregar producto al carrito
    document.querySelectorAll('.agregar-carrito').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            
            fetch('carrito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `accion=agregar&id_producto=${productId}`
            })
            .then(response => response.text())
            .then(() => {
                actualizarContadorCarrito();
                // Mostrar notificación
                const notification = document.createElement('div');
                notification.className = 'notification';
                notification.textContent = 'Producto añadido al carrito';
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.add('fade-out');
                    setTimeout(() => notification.remove(), 500);
                }, 2000);
            });
        });
    });
    
    // Actualizar contador del carrito
    function actualizarContadorCarrito() {
        fetch('includes/carrito_count.php')
            .then(response => response.json())
            .then(data => {
                const counter = document.querySelector('.carrito-count');
                if (data.count > 0) {
                    counter.textContent = data.count;
                    counter.style.display = 'flex';
                } else {
                    counter.style.display = 'none';
                }
            });
    }
    
    // Inicializar contador
    actualizarContadorCarrito();
    
    // Mostrar/ocultar información de métodos de pago
    const metodoPago = document.getElementById('metodo_pago');
    if (metodoPago) {
        metodoPago.addEventListener('change', function() {
            document.querySelectorAll('.metodo-info').forEach(div => {
                div.style.display = 'none';
            });
            
            const selectedMethod = this.value;
            document.getElementById(`${selectedMethod}-info`).style.display = 'block';
        });
        
        // Mostrar el método seleccionado al cargar
        const selectedMethod = metodoPago.value;
        document.getElementById(`${selectedMethod}-info`).style.display = 'block';
    }
});

// Notificación CSS
const style = document.createElement('style');
style.textContent = `
.notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 15px 20px;
    border-radius: 5px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    z-index: 1000;
    animation: slideIn 0.5s forwards;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.fade-out {
    animation: fadeOut 0.5s forwards;
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}
`;
document.head.appendChild(style);