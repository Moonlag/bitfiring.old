
export default function (axios){
    return {
        deposit(payload){
            return axios.post('/a/deposit', payload)
        },
        withdrawal(payload){
            return axios.post('/a/withdrawal', payload)
        },
        cancelWithdrawal(payload){
            return axios.post('/a/cancel_withdrawal', payload)
        },
        statusDeposit(){
            return axios.post('/a/ajax_status_deposit')
        },
        cancelDeposit(payload){
            return axios.post('/a/cancel_deposit', payload)
        },
    }
}
