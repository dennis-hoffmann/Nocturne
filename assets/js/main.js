import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'

import 'bootstrap'
import 'bootstrap-slider/src/js/bootstrap-slider'
import 'bootstrap-slider/src/sass/bootstrap-slider.scss'
import 'bootstrap-select/js/bootstrap-select'
import 'bootstrap-select/sass/bootstrap-select.scss'

Array.prototype.move = function(from, to) {
    this.splice(to, 0, this.splice(from, 1)[0]);
};

router.beforeEach((to, from, next) => {
    if (from.name === 'Detail' || to.name === 'Detail') {
        store.commit('toggleDetailPlayer')
    }
    next();
})

createApp(App)
    .use(router)
    .use(store)
    .mount('#app')
;