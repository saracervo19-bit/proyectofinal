<footer class="footer-principal">
    <div class="footer-container">
        <div class="footer-section">
            <a href="/index.php">
                <img src="/assets/img/Logotipo.png" alt="Logotipo Zentek" class="logo-footer">
            </a>
            <p>Expertos en tecnología dron y robótica avanzada. Llevamos el futuro a tu puerta.</p>
        </div>

        <div class="footer-section">
            <h3>Enlaces rápidos</h3>
            <ul>
                <li><a href="/blog/blog.php"><i class="fas fa-newspaper"></i> Blog</a></li>
                <li><a href="/index.php"><i class="fas fa-box"></i> Productos</a></li>
                <li><a href="/auth/perfil.php"><i class="fas fa-user"></i> Mi Cuenta</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Contacto</h3>
            <p><i class="fas fa-envelope"></i> info@zentek.es</p>
            <p><i class="fas fa-phone"></i> +34 900 123 456</p>
            <p><i class="fas fa-map-marker-alt"></i> Asturias, España</p>
        </div>
    </div>

    <div class="footer-copy">
        <p>&copy; 2026 Zentek - Proyecto Final DAW - Sara Fernández Ramos</p>
    </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Buscamos todos los formularios que envíen datos al carrito
    const formulariosCarrito = document.querySelectorAll('form[action*="accion_carrito.php"]');

    formulariosCarrito.forEach(form => {
        form.addEventListener("submit", function(e) {
            
            // 🛑 NUEVO: Comprobamos si el formulario es el de "Eliminar"
            const inputAccion = this.querySelector('input[name="accion"]');
            if (inputAccion && inputAccion.value === 'eliminar') {
                // Si es eliminar, no hacemos nada con AJAX. 
                // Dejamos que recargue la página para actualizar los precios.
                return true; 
            }

            // Si es "agregar", aplicamos la MAGIA AJAX
            e.preventDefault(); // Bloquea la recarga de la página

            const formData = new FormData(this);
            const boton = this.querySelector('button[type="submit"]');
            const textoOriginal = boton.innerHTML;

            // Enviamos los datos al servidor en segundo plano
            fetch(this.action, {
                method: 'POST',
                body: formData
            }).then(response => {
                // 1. Feedback visual en el botón (Se pone verde y dice ¡Añadido!)
                boton.innerHTML = '<i class="fas fa-check"></i> ¡Añadido!';
                boton.style.backgroundColor = 'var(--accent)'; 
                boton.style.transform = 'scale(1.05)';

                // 2. Actualizamos el numerito rojo del carrito arriba en el menú (si existe)
                const badge = document.querySelector('.badge-carrito');
                if (badge) {
                    let cantidadActual = parseInt(badge.innerText) || 0;
                    badge.innerText = cantidadActual + 1;
                }

                // 3. Devolvemos el botón a la normalidad después de 2 segundos
                setTimeout(() => {
                    boton.innerHTML = textoOriginal;
                    boton.style.backgroundColor = '';
                    boton.style.transform = '';
                }, 2000);

            }).catch(error => {
                console.error('Error:', error);
                alert("Hubo un problema al añadir el producto.");
            });
        });
    });
});
</script>
</body>
</html>