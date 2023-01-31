require('dotenv').config({path: '../.env'})
const mysql = require('mysql2');
const db = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_DATABASE,
});

class DB {
    update_payment(data) {
        return new Promise(async (resolve, reject) => {
            db.query(
                "UPDATE payments SET status = ?, source_transaction = ?, amount = ? , amount_usd = ? WHERE id = ?",
                [data.status, data.source_transaction, data.amount, data.usdt, data.id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }
    
    update_address(data) {
        return new Promise(async (resolve, reject) => {
            db.query(
                "UPDATE wallets_temp SET used = ? WHERE id = ?",
                [data.used, data.id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }

    select_payment(data) {
        return new Promise(async (resolve, reject) => {
            db.query(
                "SELECT * FROM payments WHERE id = ?",
                [data.id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows[0]);
                }
            );
        });
    }

    select_wallet(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "SELECT * FROM wallets WHERE id = ? LIMIT 1",
                [data.id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows[0]);
                }
            );
        });
    }

    bonuses_user(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "INSERT INTO bonuses_user(published, stage, user_id, currency, wager, amount, bonus_id) VALUES(?, ?, ?, ?, ?, ?, ?)",
                [1, 2, data.user_id, data.currency_id, data.wager, data.amount, data.bonus_id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }

    freespin_issue(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "INSERT INTO freespin_issue(title, player_id, currency_id, bonus_id, `count`, win, stage, status, active_until) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [data.title, data.player_id, data.currency_id, data.bonus_id, data.count, 0, 1, 1, data.active_until],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }

    bonus_issue(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "INSERT INTO bonus_issue(amount, locked_amount, fixed_amount, user_id, currency_id, bonus_id, reference_id, to_wager, wagered, stage, status, active_until) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [data.amount, data.locked_amount, data.fixed_amount, data.user_id, data.currency_id, data.bonus_id, 0, data.to_wager, 0, 2, 2, data.active_until],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }

    bonus_issue_history(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "INSERT INTO bonus_issue_history(player_id, bi_id, strategy_id, group_key, status) VALUES (?, ?, ?, ?, ?)",
                [data.user, data.bi_id, 3, '', 1],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }

    get_last_active_bonus_issue(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "SELECT * FROM bonus_issue WHERE user_id = ? AND status = 2 ORDER BY id DESC",
                [data.user_id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows[0]);
                }
            );
        });
    }

    update_bonus_issue(data){
        return new Promise((resolve, reject) => {
            db.query(
                "UPDATE bonus_issue SET status = ? WHERE id = ?",
                [data.status, data.id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }

    update_wallet(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "UPDATE wallets SET balance = ? WHERE id = ?",
                [data.new_balance, data.id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }

    update_bonus_option(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "UPDATE bonus_options SET enabled = ? WHERE id = ?",
                [data.enabled, data.id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }

    update_wallet_temp(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "UPDATE wallets_temp SET used = ? WHERE id = ?",
                [data.used, data.address_id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }

    select_hash_transaction(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "SELECT COUNT(id) as `count` FROM payments WHERE source_transaction = ?",
                [data.hash],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }

    select_currency(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "SELECT code, rate FROM currency WHERE id = ?",
                [data.id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows[0]);
                }
            );
        });
    }

    get_wallet(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "SELECT * FROM wallets WHERE user_id = ? AND balance > 0",
                [data.id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows[0]);
                }
            );
        });
    }


    get_bets(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "SELECT * FROM games_bets WHERE user_id = ?",
                [data.id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }

    swap(data) {
        return new Promise((resolve, reject) => {
            db.query(
                "INSERT INTO swaps(from_id, to_id, from_amount, to_amount) VALUES (?, ?, ?, ?)",
                [data.from_id, data.to_id, data.from_amount, data.to_amount],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }

    wallet_swap(data){
        return new Promise((resolve, reject) => {
            db.query(
                "SELECT * FROM wallets WHERE user_id = ? AND currency_id = ?",
                [data.user_id, data.currency_id],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows[0]);
                }
            );
        });
    }

    transactions_swap(data){
        return new Promise((resolve, reject) => {
            db.query(
                "INSERT INTO transactions(player_id, currency_id, amount, reference_id, reference_type_id, type_id, wallet_id, bonus_part) VALUES (?, ?, ?, ?, ? ,?, ?, ?)",
                [data.player_id, data.currency_id, data.amount, data.reference_id, 9, data.type_id, data.wallet_id, 0],
                function (err, rows) {
                    if (err) reject(new Error(err));
                    else resolve(rows);
                }
            );
        });
    }
}

module.exports = DB;
