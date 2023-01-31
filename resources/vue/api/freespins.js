

export default function (axios){
    return {
        history(payload = null){
            return axios.post('/a/user/freespins', payload)
        }
    }
}
