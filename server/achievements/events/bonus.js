const {bonusAmount} = require('../mysql/database')

const achievements = {
    6: 100,
    7: 500,
    8: 1000,
    9: 3333,
    10: 7777,
}

module.exports = {
    async execute({user_id}) {
        const achievements_id = [];
        const amount = await bonusAmount(user_id)

        for (const id in achievements){
            if(amount >= achievements[id]){
                achievements_id.push(id)
            }
        }

        return achievements_id;
    }
}
