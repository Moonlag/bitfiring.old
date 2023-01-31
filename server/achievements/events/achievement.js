const {countAchievement, getAllAchievements} = require('../mysql/database')

const achievements = {
    31: 10,
    32: 25,
    33: 50,
    34: '*',
}

module.exports = {
    async execute({user_id}){
        const achievements_id = [];
        const amount = await countAchievement(user_id)

        for (const id in achievements){
            if(achievements[id] === '*'){
                achievements[id] = (await getAllAchievements()).length
            }

            if(amount >= achievements[id]){
                achievements_id.push(id)
            }
        }

        return achievements_id;
    }
}
