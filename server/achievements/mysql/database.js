require('dotenv').config({path: `${__dirname}/../../../.env`})
const mysql = require('mysql2');

const pool = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_DATABASE,
}).promise();

async function getPlayer(id)
{
    const [rows] = await pool.query(`
        SELECT *
        FROM players
        WHERE id = ?
    `, [id])
    return rows[0]
}

async function getBonusIssue(id, status = [2, 3])
{
    const [rows] = await pool.query(`
        SELECT *
        FROM bonus_issue
        WHERE user_id = ?
          AND status IN (?)
    `, [id, status])
    return rows
}

async function getBets(id, status = [])
{
    const [rows] = await pool.query(`
        SELECT *
        FROM games_bets
        WHERE user_id = ?
          AND status IN (?)
    `, [id, status])
    return rows
}

async function getAchievement(id) {
    const [rows] = await pool.query(`
        SELECT *
        FROM player_achievements
        WHERE player_id = ?
    `, [id])
    return rows
}

async function checkAchievement(id, achievements) {
    const [rows] = await pool.query(`
        SELECT *
        FROM player_achievements
        WHERE player_id = ?
          AND achievement_id IN (?)
    `, [id, achievements])
    return rows
}

async function insertAchievements(data) {
    const [rows] = await pool.query(`
          INSERT INTO player_achievements (achievement_id, player_id) VALUES ?
          `, [data])
    return rows
}

async function handlerAchievements(user_id, success){
    const currentAchievement = await checkAchievement(user_id, success);

    const newAchievements = success
        .filter(
            (el) => !currentAchievement.some((ach) => ach.achievement_id !== el)
        ).map(
            (el) => {
                return [
                    el,
                    user_id
                ]
            }
        );

    if(newAchievements.length) {
        await insertAchievements(newAchievements);
    }

    return newAchievements;
}

async function getAllAchievements() {
    const [rows] = await pool.query(`
        SELECT *
        FROM achievements
        WHERE player_id = ?
    `,)
    return rows
}

async function getCatGames(id){
    const [rows] = await pool.query(`
        SELECT *
        FROM games_cats AS gc
        INNER JOIN game_category_binds AS gcb ON gc.id = gcb.category_id
        INNER JOIN games AS g ON gcb.game_id = g.id
        WHERE gc.id = ?
    `, [id])

    return rows
}

async function wagered(id) {
    const rows = await getBonusIssue(id)
    return rows.map((el) => Number(el.wagered)).reduce((a, b) => a + b, 0)
}

async function bonusAmount(id) {
    const rows = await getBonusIssue(id)
    return rows.map((el) => Number(el.amount)).reduce((a, b) => a + b, 0)
}

async function betSum(id) {
    const rows = await getBets(id, [1])
    return rows.map((el) => Number(el.bet_sum)).reduce((a, b) => a + b, 0)
}

async function bets(id, status = [1]) {
    const rows = await getBets(id, status)
    return rows
}

async function betWin(id) {
    const rows = await getBets(id, [3])
    return rows.map((el) => Math.abs(Number(el.profit))).reduce((a, b) => a + b, 0)
}

async function countAchievement(id) {
    const rows = await getAchievement(id)
    return rows.length
}


async function categoryWin(id, user_id, bet) {
    const games = await getCatGames(id)
    games.map((el) => el.id)

    const rows = await getBets(user_id, [3])
    return rows.filter((el) => games.includes(el.id) && Number(el.profit) > bet).length
}


module.exports = {
    wagered,
    bonusAmount,
    bets,
    categoryWin,
    countAchievement,
    getAllAchievements,
    handlerAchievements,
}
