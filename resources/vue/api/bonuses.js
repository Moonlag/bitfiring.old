
export default function (axios){
    return {
        history(payload = {}){
            return axios.post('/a/user/history_bonuses', payload)
        },
        all(){
            return axios.post('/a/bonuses')
        },
        user(){
            return axios.post('/a/user/bonuses')
        },
    }
}
