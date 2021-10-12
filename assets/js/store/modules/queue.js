const state = {
    songs: [],
    active: null,
};

const getters = {
    songs: state => state.songs,
    active: state => state.active,
};

const actions = {
    async fetchQueue({ commit }) {
        let res = await fetch(`/library/audio/userplaylist`, {
            method: 'GET',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        commit('setQueue', await res.json());
    },

    async addToQueue({ dispatch, commit }, song) {
        commit('appendToQueue', song);
        commit('setQueue', state.songs)
    },

    async addSongsToQueue({ dispatch, commit }, songs) {
        songs.forEach(songToAdd => {
            commit('appendToQueue', songToAdd);
        });

        commit('setQueue')
    },

    moveItem({ commit, state }, params) {
        state.songs.move(params.from, params.to);

        commit('setQueue');
    },

    playQueueItem({ dispatch, commit }, song) {
        commit('setCurrentSong', song, { root: true });
        commit('player/play', null, { root: true });
    }
};

const mutations = {
    setQueue(state, songs) {
        if (songs) {
            state.songs = songs;
        }

        // Asynchronously update remote
        fetch(`/library/audio/userplaylist`, {
            method: 'POST',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            },
            body: JSON.stringify(
                state.songs.map(song => song.id)
            )
        });

    },
    appendToQueue(state, songToAdd) {
        const index = state.songs.findIndex(song => song.id === songToAdd.id);
        if (index !== -1) {
            state.songs.splice(index, 1);
        }

        state.songs.push(songToAdd);
    },
    moveToNextIndex(state, song) {
        const existingQueueIndex = state.songs.findIndex(queueItem => song.id === queueItem.id);
        const currentPlayingSongIndex = this.state.currentSong
            ? state.songs.findIndex(queueItem => this.state.currentSong.id === queueItem.id)
            : -1
        ;

        if (existingQueueIndex !== -1) {
            state.songs.move(existingQueueIndex, currentPlayingSongIndex+1)
        } else {
            state.songs.splice(currentPlayingSongIndex+1, 0, song);
        }
    },
    deleteQueueItem(state, item) {
        const index = state.songs.findIndex(song => song.id === item.id);
        if (state.songs[index]) {
            state.songs.splice(index, 1);
        }
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};