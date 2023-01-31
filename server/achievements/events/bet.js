const {bets} = require('../mysql/database')

const achievements = {
    11: 0.25,
    12: 1,
    13: 5,
    14: 12.5,
    15: 50,
}

module.exports = {
    async execute({user_id}) {
        const achievements_id = [];
        const amount = await bets(user_id)

        for (const id in achievements){
            if(amount.filter((el) => el?.bet_sum >= achievements[id]).length >= 20){
                achievements_id.push(id)
            }
        }

        return achievements_id;
    }
}
