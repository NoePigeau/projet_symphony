const slug = window.location.pathname.split('/')[2]

const deleteIcone = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_6_12261)">
<path d="M15.73 3H8.27L3 8.27V15.73L8.27 21H15.73L21 15.73V8.27L15.73 3ZM17 15.74L15.74 17L12 13.26L8.26 17L7 15.74L10.74 12L7 8.26L8.26 7L12 10.74L15.74 7L17 8.26L13.26 12L17 15.74Z" fill="#ff4040"/>
</g>
<defs>
<clipPath id="clip0_6_12261">
<rect width="24" height="24" fill="white"/>
</clipPath>
</defs>
</svg>
`

window.addEventListener("load", async () => {
    await displaySteps()
    const handleStepBtn = document.getElementById('handle-steps')
    if(handleStepBtn) {
        handleStepBtn.addEventListener('click', () => {
            editSteps()
        })
    }
})

// http request

const getSteps = async () => {
    return fetch(`/step/${slug}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
    })
    .then(res => res.json())
    .then(res => res.sort((step1, step2) => step1.position < step2.position))
    .catch(err => { console.error(err) })
}

const toggleStep = async (e, id) => {
    e.target.disabled = true
    await fetch(`/step/${id}/toggle`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
    }).catch(err => { console.error(err) })
    e.target.disabled = false
}

const submitSteps = async () => {
    const updatedStepsDiv = document.querySelectorAll('#steps .step .input')
    const updatedSteps = [] 
    updatedStepsDiv.forEach((input, index) => {
        updatedSteps.push({
            position: index,
            name: input.value,
            status: input.getAttribute('ischeck') ?? false
        }) 
    })
    await fetch(`/step/${slug}/update`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(updatedSteps)
    })
    .catch(err => { console.error(err) })
    await displaySteps()
}



// handle html

const displaySteps = async () => {
    const stepsContainer = document.getElementById('steps')
    if (!stepsContainer) {
        return
    }
    const handleStepBtn = document.getElementById('handle-steps')
    if (editBtn) {
        editBtn.style.display = "flex"
    }
    document.getElementById('edit-btns').innerHTML = ""
    document.getElementById('edit-btns').style.display = "none"

    const steps = await getSteps()
    stepsContainer.innerHTML = ""
    steps.forEach((step) => {
        const el = document.createElement('div')
        el.classList.add('step')
        const input = document.createElement('input')
        input.type = 'checkbox'
        input.checked = step.status
        input.addEventListener('click' , async (e) => {
            if(!e.target.disabled) {
                await toggleStep(e, step.id)
            }
        })
        el.appendChild(input)
        el.append(step.name)

        stepsContainer.appendChild(el)
    })
}

const displayBtnEdit = () => {
    const container = document.getElementById('edit-btns')
    container.style.display = "flex"
    document.getElementById('handle-steps').style.display = "none"
    const actions = document.createElement('div')
    actions.classList.add('actions')

    const cancel = document.createElement('button')
    cancel.innerText = 'cancel'
    cancel.classList.add('btn', 'cancel')
    cancel.addEventListener('click', displaySteps)

    const newStepBtn = document.createElement('button')
    newStepBtn.innerText = 'new'
    newStepBtn.classList.add('btn', 'validate')
    newStepBtn.addEventListener('click', addNewInput)

    const validate = document.createElement('button')
    validate.innerText = 'validate'
    validate.classList.add('btn', 'validate')
    validate.addEventListener('click', submitSteps)

    actions.appendChild(cancel)
    actions.appendChild(newStepBtn)
    actions.appendChild(validate)
    container.appendChild(actions)
}

const createInputContainer = (step, stepsContainer, index) => {
    const el = document.createElement('div')
    el.classList.add('step')

    const input = document.createElement('input')
    input.value = step.name
    input.classList.add('input')
    input.setAttribute('ischeck', step.status)
    el.appendChild(input)

    const btnDelete = document.createElement('button')
    btnDelete.innerHTML = deleteIcone
    btnDelete.addEventListener('click', (e) => {
        el.parentElement.removeChild(el)
    })
    el.appendChild(btnDelete)
    return el
}

const addNewInput = () => {
    const stepsContainer = document.getElementById('steps')
    const steps =  document.querySelectorAll('#steps .step')
    const el = createInputContainer({name: '', status: false}, stepsContainer, steps.length)      
    stepsContainer.appendChild(el)
}

const editSteps = async () => {
    const stepsContainer = document.getElementById('steps')
    stepsContainer.innerHTML = "loading ..."

    const steps = await getSteps()
    stepsContainer.innerHTML = ""

    steps.forEach((step, index) => {
        const el = createInputContainer(step, stepsContainer, index)  
        stepsContainer.appendChild(el)
    })

    displayBtnEdit(stepsContainer)
}


