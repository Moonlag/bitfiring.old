
const URL = API_BASE_URL || ''

export default {
    install: (app, option) => {
        app.config.globalProperties.$cdn = URL
        app.provide('cdn', URL)
    }
}
