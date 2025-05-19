<script>
    document.getElementById('true-false-form').addEventListener('submit', function(event) {
        event.preventDefault();

        // Get the question, island, and level from the form
        const questionText = document.getElementById('question-text').value;
        const selectedIsland = document.getElementById('isla-select').value;
        const selectedLevel = document.getElementById('level-select').value;

        // Update the preview section
        document.getElementById('preview-question').textContent = `Pregunta: ${questionText}`;
        const previewIsland = document.getElementById('preview-island');
        const previewLevel = document.getElementById('preview-level');

        if (!previewIsland) {
            const islandElement = document.createElement('p');
            islandElement.id = 'preview-island';
            islandElement.textContent = `Isla: ${selectedIsland}`;
            document.querySelector('.preview-box').appendChild(islandElement);
        } else {
            previewIsland.textContent = `Isla: ${selectedIsland}`;
        }

        if (!previewLevel) {
            const levelElement = document.createElement('p');
            levelElement.id = 'preview-level';
            levelElement.textContent = `Nivel: ${selectedLevel}`;
            document.querySelector('.preview-box').appendChild(levelElement);
        } else {
            previewLevel.textContent = `Nivel: ${selectedLevel}`;
        }

        // Show an alert to confirm the question was saved
        alert('Pregunta guardada con éxito!');
    });

    // Add event listeners to the True and False buttons
    const trueButton = document.getElementById('preview-true-button');
    const falseButton = document.getElementById('preview-false-button');

    trueButton.addEventListener('click', function() {
        trueButton.classList.add('selected');
        falseButton.classList.remove('selected');
        alert('Seleccionaste: Verdadero');
    });

    falseButton.addEventListener('click', function() {
        falseButton.classList.add('selected');
        trueButton.classList.remove('selected');
        alert('Seleccionaste: Falso');
    });
</script>
