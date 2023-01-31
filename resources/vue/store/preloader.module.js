export const preloader = {
    namespaced: true,
    state: {
        loader: true,
        open: null,
        title: null,
        timeout: null,
    },
    actions: {

    },
    mutations: {
        open_loader(state){
            state.title = null
            state.loader = true
        },
        close_loader(state){
            state.loader = false
            const el = document.body;
            el.classList.remove('fancybox-active', 'compensate-for-scrollbar');
        },
        SET_TITLE(state, payload){
            state.title = payload
        },
        SET_TIMEOUT(state, payload){
            state.timeout = payload
        },
        CLEAR_TIMEOUT(state){
            clearTimeout(state.timeout)
        }
    }
};
