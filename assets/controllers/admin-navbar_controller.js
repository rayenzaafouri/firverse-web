////
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        console.log("Admin navbar module attached")
        let accentMap = {
            gym: "#3478F6",
            nutrition: "#AEC867",
            exercise: "#69D2C8",
            shop: "#F09A37",
            user: "#545454",
            events: "#EB4E3D",
            default: "#9FF85",
        };
        updateAccentColor(accentMap["default"])

        let items = Array.from(
            document.querySelectorAll(".fitverse-navbar .nav-item")
        );
        items.forEach((item) => {
            item.addEventListener("mouseover", (event) => {
                let newContext = item.getAttribute("data-context");

                if (newContext && accentMap[newContext]) {
                    updateAccentColor(accentMap[newContext]);
                }
            });

            item.addEventListener("mouseover", (event) => {
                let newContext = item.getAttribute("data-context");

                if (newContext && accentMap[newContext]) {
                    updateAccentColor(accentMap[newContext]);
                }
            });
        });

        items.forEach((item) => {
            item.addEventListener("mouseout", (event) => {
                updateAccentColor(accentMap[currentContext]);
            });
        });

        function updateAccentColor(hexValue) {
            document
                .querySelector("nav.fitverse-navbar")
                .style.setProperty("border-color", hexValue);
        }

        // User menu toggle
        document.querySelector(".user-nav-menu").addEventListener("click", () => {
            toggleNavbarState();
        });

        // Adding delay animation to each item for smooth roll
        let time_offset = 0.1;
        let iteration = 0;
        let animatable_items = Array.from(
            document.querySelectorAll(".user-nav-menu-content > ul > li")
        ).forEach((item) => {
            if (iteration > 0) {
                item.style.setProperty("animation-delay", iteration * time_offset + "s");
            }
            iteration++;
        });

        function toggleNavbarState() {
            if (!getNavbarState()) {
                showUserMenu();
                return;
            }
            closeUserMenu();
        }

        function getNavbarState() {
            return document.querySelector(".user-nav-menu").classList.contains("open");
        }

        function showUserMenu() {
            document.querySelector(".user-nav-menu").classList.add("open");
            document.querySelector(".user-nav-menu-content").classList.add("open");
        }

        function closeUserMenu() {
            document.querySelector(".user-nav-menu").classList.remove("open");
            document.querySelector(".user-nav-menu-content").classList.remove("open");
        }

        console.log("Admin navbar module successfully loaded")
    
    
    
    }
 





}

/////
