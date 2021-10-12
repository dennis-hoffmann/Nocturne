const state = {
    filters: null,
};

const getters = {
    filters: state => state.filters,
};

const actions = {
    async fetchFilterValues({ commit, dispatch }) {
        const res = await fetch('/library/audio/filters', {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        })

        commit('setFilters', await res.json());
    }
};

const mutations = {
    setFilters(state, filters) {
        state.filters = filters;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};