export default function (axios){
    return {
        new(payload){
            return axios.post('/a/user/set_new_wallet', payload)
        },
        setPrimary(payload){
            return axios.post('/a/user/set_wallet', payload)
        }
    }
}
