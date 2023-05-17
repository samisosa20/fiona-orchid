import {controllerRelation } from '../controllers'
const pageMovement = () => {
    if (window.location.pathname === "/movement/create") {

        console.log('run movement page')
        controllerRelation()
        
        const handleChangeType = (value) => {
            // if is transfer mode
            const account_id = document.querySelector('[name="movement\\[account_id\\]"')
            const category_id = document.querySelector('[name="movement\\[category_id\\]"')
            const account_end_id = document.querySelector('[name="movement\\[account_end_id\\]"')
            const label_account_id = account_id.parentNode.parentNode.querySelector('label');
            if(value == 1) {
                document.querySelector('#container-account_in').classList.remove('d-none')
                document.querySelector('#container-movement').classList.add('d-none')
                label_account_id.innerHTML = 'Account out<sup class="text-danger">*</sup>';
                category_id.removeAttribute('required', true);
                account_end_id.setAttribute('required', true);
            } else {
                document.querySelector('#container-movement').classList.remove('d-none')
                document.querySelector('#container-account_in').classList.add('d-none')
                label_account_id.innerHTML = 'Account<sup class="text-danger">*</sup>';
                category_id.setAttribute('required', true);
                account_end_id.removeAttribute('required', true);
            }
        }

        const type = document.querySelector('[name="movement\\[type\\]"')
        type.addEventListener('change', (e) => handleChangeType(e.target.value))
        handleChangeType(type.value)
    }
}

document.addEventListener("turbo:load", () => {
    pageMovement()
})

window.addEventListener('DOMContentLoaded', () => {
    pageMovement()
})
