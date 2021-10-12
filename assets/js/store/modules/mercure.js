const state = {
    updates: []
};

const getters = {
    updates: state => state.updates,
};

const actions = {};

const mutations = {
    pushUpdate(state, update) {
        state.updates.push(update);
    },
    dismissUpdate(state, id) {
        state.updates.splice(id, 1)
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};