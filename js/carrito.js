document.addEventListener('DOMContentLoaded', function() {
    // Eliminar producto del carrito
    document.querySelectorAll('.eliminar').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-id');
            
            if (confirm('¿Estás seguro de eliminar este producto del carrito?')) {
                const form = this.closest('form');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id_producto';
                input.value = productId;
                form.appendChild(input);
                
                form.submit();
            }
        });
    });
    
    // Validar cantidades antes de actualizar
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (e.submitter && e.submitter.value === 'actualizar') {
                let valid = true;
                document.querySelectorAll('input[type="number"]').forEach(input => {
                    if (input.value <= 0 || isNaN(input.value)) {
                        valid = false;
                        input.style.borderColor = 'red';
                    } else {
                        input.style.borderColor = '';
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert('Por favor ingresa cantidades válidas (mayores a 0)');
                }
            }
        });
    }
});