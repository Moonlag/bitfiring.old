/* abstract */
class SessionStore {
    findSession(id) {
    }

    saveSession(id, session) {
    }

    findAllSessions() {
    }

    removeSession(id) {

    }
}

class InMemorySessionStore extends SessionStore {
    constructor() {
        super();
        this.sessions = new Map();
    }

    findSession(id) {
        return this.sessions.get(id);
    }

    saveSession(id, session) {
        this.sessions.set(id, session);
    }

    findAllSessions() {
        return [...this.sessions.values()];
    }

    removeSession(id) {
        this.sessions.delete(id);
    }
}

module.exports = {
    InMemorySessionStore
};
