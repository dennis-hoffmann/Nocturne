const state = {
    ids: {
        albums: [],
        songs: [],
        artists: [],
    },
    sections: {
        recentlyListened: {
            albums: [],
            songs: [],
        },
        newSongs: {
            albums: [],
            songs: [],
        },
        allAlbums: {
            albums: [],
            songs: [],
        },
    },
    searchResults: {},
    userPlaylists: null
};

const getters = {
    getSongsBySection: (state) => (section) => {
        return state.sections[section].songs ?? []
    },
    getSongsByAlbumId: (state) => (id) => {
        return state.ids.albums[id].songs ?? []
    },
    getAlbumsBySection: (state) => (section) => {
        return state.sections[section].albums ?? []
    },
    getAlbumById: (state) => (id) => {
        return state.ids.albums[id] ?? null
    },
    getArtistById: (state) => (id) => {
        return state.ids.artists[id] ?? null;
    },
    getSearchResult: (state) => (query) => {
        return state.searchResults[query] ?? { albums: [], songs: [] }
    },
    getUserPlaylists: (state) => (query) => {
        return state.userPlaylists ?? [];
    },
};

const actions = {
    async fetchLibrarySection ({ commit, state }, section) {
        const payload = {
            section,
            data: {
                albums: [],
                songs: [],
            },
        };

        switch (section) {
            case 'recentlyListened':
                payload.data.songs = await this.state.lib.getUserRecentlyListened();
                break;
            case 'newSongs':
                payload.data = await this.state.lib.getNewSongs();
                break;
            case 'allAlbums':
                payload.data.albums = await this.state.lib.getAlbums();
                break;
        }

        commit('setSection', payload);
    },

    async fetchAlbumById ({ commit, state }, id) {
        if (!state.ids.albums[id]) {
            const album = await this.state.lib.findAlbum(id);

            if (!album) {
                throw new Error(`Album by id ${id} not found`);
            }

            commit('addAlbumForId', album)
        }
    },

    async fetchArtistById ({ commit, state }, id) {
        if (!state.ids.artists[id]) {
            const artist = await this.state.lib.findArtist(id);

            if (!artist) {
                throw new Error(`Artist by id ${id} not found`);
            }

            commit('addArtistForId', artist)
        }
    },

    async fetchSongById ({ commit, state }, id) {
        if (!state.ids.songs[id]) {
            const song = await this.state.lib.findSong(id);

            if (!song) {
                throw new Error(`Song by id ${id} not found`);
            }

            commit('addSongForId', song)
        }
    },

    async fetchSearchResult ({ commit, state }, query) {
        if (!state.searchResults[query]) {
            const result = await this.state.lib.search(query);

            commit('setSearchResultForQuery', {query, result})
        }
    },

    async fetchUserPlaylists({ commit, state }) {
        if (state.userPlaylists === null) {
            const result = await this.state.lib.getUserPlaylists();

            commit('setUserPlaylists', result)
        }
    }
};

const mutations = {
    setSection (state, payload) {
        state.sections[payload.section] = payload.data
    },
    addSongForId (state, song) {
        state.ids.songs[song.id] = song;
    },
    addAlbumForId (state, album) {
        state.ids.albums[album.id] = album;
    },
    addArtistForId (state, artist) {
        state.ids.artists[artist.id] = artist;
    },
    setSearchResultForQuery (state, data) {
        state.searchResults[data.query] = data.result;
    },
    setUserPlaylists (state, playlists) {
        state.userPlaylists = playlists;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};