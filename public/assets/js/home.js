const decreaseBtn = document.getElementById('decrease-btn');
const increaseBtn = document.getElementById('increase-btn');
const spanNumber = document.getElementById('number');

let num = 0;

decreaseBtn.addEventListener('click', () => {
    num--;
    spanNumber.innerText = num;
});

increaseBtn.addEventListener('click', () => {
    num++;
    spanNumber.innerText = num;
});

spanNumber.innerText = num;