import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {

		let loginSwitch = document.querySelector("#loginSwitch")
        let registerSwitch = document.querySelector("#registerSwitch")


        let loginForm = document.querySelector("#loginForm")
        let registerForm = document.querySelector("#registerForm")


        loginSwitch.addEventListener("click", showLogin)
        registerSwitch.addEventListener("click", showRegister)


        function showLogin() {
            loginSwitch.classList.add("active")
            registerSwitch.classList.remove("active")


            registerForm.style.setProperty("display", "none")
            loginForm.style.setProperty("display", "unset")

        }

        function showRegister() {
            registerSwitch.classList.add("active")
            loginSwitch.classList.remove("active")

            loginForm.style.setProperty("display", "none")
            registerForm.style.setProperty("display", "unset")
        }

        console.log("Auth script loaded correctly");
        
       



    }
}
