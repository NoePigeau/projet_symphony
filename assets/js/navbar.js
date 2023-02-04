window.addEventListener("load", () => {
    const burger = document.getElementById('burger-menu')
    burger.addEventListener('click', () => {
        document.querySelector('.navbar-front').classList.toggle('show')
    })
})

