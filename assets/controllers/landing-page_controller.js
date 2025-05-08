////
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        console.log("Landing module attached")

        document.addEventListener('DOMContentLoaded', () => {


            //Shop swiper setup
            const shopSwiper = new Swiper('.shop-swiper', {
                direction: 'horizontal',
                loop: true,
                grabCursor: true,
                slidesPerView: 4,
                spaceBetween: 100,
                autoplay: {
                    delay: 1200
                },

            });


            //Shop swiper setup
            const gymSwiper = new Swiper('.gym-swiper', {
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                slidesPerView: 1,
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },

            });


            //Gym
            const typed = new Typed('#gym-search-animation', {
                strings: ['California Gym', 'HardBeat Athletics', 'Volcano Gym', 'Xtra BBF'],
                typeSpeed: 100,
                loop: true,
                backDelay: 1000, // Pause before deleting
                onStringTyped: function (index, self) {
                    showScreenshot(index)
                }
            });

        });

        //Gym search animation image switcher
        function showScreenshot(index) {
            const elements = Array.from(document.querySelector("#screenshots-wrapper").querySelectorAll(".screenshot-item"))
            if (index > elements.length - 1) {
                console.error("Cannot switch to desired screenshot : index out of bounds")
                return
            }
            const next_element = elements.splice(index, 1)


            elements.forEach(ele => {
                ele.classList.remove("screenshot-active")
                ele.classList.add("screenshot-inactive")
            })

            next_element[0].classList.add("screenshot-active")
        }


    }
}


/////