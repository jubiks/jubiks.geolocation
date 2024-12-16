document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ipSearch');
    const errorMessage = document.getElementById('error-message');

    form.addEventListener('submit', function (event) {
        // Валидируем IPv4-адрес
        const ipv4Input = document.getElementById('InputIPv4');
        const ipv4Pattern = /^(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}$/;

        if (!ipv4Input.value.match(ipv4Pattern)) {
            event.preventDefault();
            errorMessage.style.display = 'inline';
            ipv4Input.style.borderColor = 'red';
            return;
        } else {
            errorMessage.style.display = 'none';
            ipv4Input.style.borderColor = '';
        }

        // Если JavaScript работает, отправляем форму через AJAX
        event.preventDefault();
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
        })
            .then(response => {
                if (!response.ok) throw new Error('Ошибка сети');
                return response.json();
            })
            .then(data => {
                console.log('Ответ сервера:', data);

                // Отображаем форматированный JSON на странице
                const resultContainer = document.getElementById('result');
                resultContainer.textContent = JSON.stringify(data.result, null, 2);
                resultContainer.style.display = 'block';
            })
            .catch(error => {
                console.error('Ошибка:', error);

                // Отображаем ошибку
                const resultContainer = document.getElementById('result');
                resultContainer.textContent = `Ошибка: ${error.message}`;
                resultContainer.style.display = 'block';
            });
    });
});