document.addEventListener('DOMContentLoaded', () => {
    const snowflakeCount = 50; // Número de copos de nieve
    for (let i = 0; i < snowflakeCount; i++) {
        const snowflake = document.createElement('div');
        snowflake.classList.add('snowflake');
        snowflake.textContent = '❄';

        // Posicionamiento inicial
        snowflake.style.left = Math.random() * window.innerWidth + 'px';
        snowflake.style.animationDuration = Math.random() * 3 + 7 + 's'; // Duración aleatoria de la animación
        snowflake.style.fontSize = Math.random() * 10 + 10 + 'px'; // Tamaño aleatorio
        snowflake.style.animationDelay = Math.random() * 5 + 's'; // Retraso aleatorio

        // Agregar al documento
        document.documentElement.appendChild(snowflake); // Cambiar a documentElement
    }
});
