window.addEventListener("load", () => {
    const burger = document.getElementById('burger-menu')
    if (burger) {
        burger.addEventListener('click', () => {
            document.querySelector('.navbar-front').classList.toggle('show')
        })
    }
})

