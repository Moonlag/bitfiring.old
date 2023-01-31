const {wagered} = require('../mysql/database')

const achievements = {
    5: 77777,
    4: 20000,
    3: 7500,
    2: 1000,
    1: 100,
}

module.exports = {
    async execute({user_id}) {
        const achievements_id = [];
        const amount = 7500

        for (const id in achievements){
            if(amount >= achievements[id]){
                achievements_id.push(id)
            }
        }

        return achievements_id;
    }
}
