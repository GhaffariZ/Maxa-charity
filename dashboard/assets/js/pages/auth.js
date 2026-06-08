'use strict';

let inputs = document.querySelectorAll('.form-code-input');
inputs.forEach((input, index) => {
    input.addEventListener('keyup', (e) => {
        if (e.keyCode === 8 && index !== 0) {
            inputs[index - 1].focus();
        } else if (e.keyCode !== 8 && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }
    });
});   