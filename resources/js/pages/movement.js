import {controllerRelation } from '../controllers'
const pageMovement = () => {
    if (window.location.pathname === "/movement/create" || /^\/movement\/.+\/edit$/.test(window.location.pathname)) {

        console.log('run movement page')
        controllerRelation()

        const listAccountBadge = JSON.parse(document.querySelector('#accounts-badge').value)
        
        const handleChangeType = (value) => {
            console.log(value)
            // if is transfer mode
            const account_id = document.querySelector('[name="movement\\[account_id\\]"')
            const category_id = document.querySelector('[name="movement\\[category_id\\]"')
            const account_end_id = document.querySelector('[name="movement\\[account_end_id\\]"')
            const label_account_id = account_id.parentNode.parentNode.querySelector('label');
            if(value === 'transfer') {
                document.querySelector('#container-account_in').classList.remove('d-none')
                document.querySelector('#container-movement').classList.add('d-none')
                label_account_id.innerHTML = 'Account out<sup class="text-danger">*</sup>';
                category_id.removeAttribute('required', true);
                account_end_id.setAttribute('required', true);
                handleChangeAccounts()
            } else {
                document.querySelector('#container-movement').classList.remove('d-none')
                document.querySelector('#container-account_in').classList.add('d-none')
                label_account_id.innerHTML = 'Account<sup class="text-danger">*</sup>';
                category_id.setAttribute('required', true);
                account_end_id.removeAttribute('required', true);
                document.querySelector('#container-amount-end').classList.add('d-none')
            }
        }

        const handleChangeAccounts = () => {
            const accountOut = document.querySelector('[name="movement\\[account_id\\]"').value
            const amountIn = document.querySelector('[name="movement\\[account_end_id\\]"').value

            const badgeAccountIn = amountIn ? listAccountBadge.find(v => v.id == amountIn)?.badge_id : null
            const badgeAccountOut = accountOut ? listAccountBadge.find(v => v.id == accountOut)?.badge_id : null

            if(badgeAccountOut && badgeAccountIn) {
                if(badgeAccountIn === badgeAccountOut){
                    console.log('Iguales')
                    document.querySelector('#container-amount-end').classList.add('d-none')
                } else {
                    document.querySelector('#container-amount-end').classList.remove('d-none')
                    console.log('No Iguales')
                }
            }
        }

        const types = document.querySelectorAll('[name="movement\\[type\\]"')
        const accountStart = document.querySelector('[name="movement\\[account_id\\]"')
        const accountEnd = document.querySelector('[name="movement\\[account_end_id\\]"')

        types.forEach(elem => elem.addEventListener('change', (e) => handleChangeType(e.target.value)))
        accountStart.addEventListener('change', () => handleChangeAccounts())
        accountEnd.addEventListener('change', () => handleChangeAccounts())
        for (const radio of types) {
            if (radio.checked) {
              handleChangeType(radio.value)
              break;
            }
          }
    }
}

document.addEventListener("turbo:load", () => {
    pageMovement()
})

window.addEventListener('DOMContentLoaded', () => {
    pageMovement()
})
