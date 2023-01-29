window.addEventListener("load", () => {
    console.log("loaded!")
    const burger = document.getElementById('burger-menu')
    console.log(burger)
    burger.addEventListener('click', () => {
        document.querySelector('.navbar-front').classList.toggle('show')
    })
})

