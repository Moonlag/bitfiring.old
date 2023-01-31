const {bets} = require('../mysql/database')

const achievements = {
    35: 15,
    36: 100,
    37: 500,
    38: 1250,
    39: 7777,
}

module.exports = {
    async execute({user_id}) {
        const achievements_id = [];
        const amount = await bets(user_id)

        for (const id in achievements){
            if(amount.filter((el) => el.bet_sum >= 0.5).length >= achievements[id]){
                achievements_id.push(id)
            }
        }

        return achievements_id;
    }
}
