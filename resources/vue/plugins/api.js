import api from '../api/index'

export default {
    install: (app, option) => {
        app.config.globalProperties.$api = api
        app.provide('api', api)
    }
}
