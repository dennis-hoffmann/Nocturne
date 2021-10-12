export default class MusicLibrary {
    constructor () {
        this.cache = {
            search: [],
            song: [],
            album: [],
            artist: [],
            albums: null,
            artists: null,
            recent: null,
            recommendations: null,
            newSongs: null
        };
    }

    async search(query) {
        if (!this.cache.search[query]) {
            let res = await fetch(`/library/audio/search/${query}`, {
                method: 'GET',
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.cache.search[query] = await res.json();
        }

        return this.cache.search[query];
    }

    async findSong(id) {
        if (!this.cache.song[id]) {
            let res = await fetch(`/library/audio/song/${id}`, {
                method: 'GET',
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.cache.song[id] = await res.json();
        }

        return this.cache.song[id];
    }

    async findAlbum(id) {
        if (!this.cache.album[id]) {
            let res = await fetch(`/library/audio/album/${id}`, {
                method: 'GET',
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.cache.album[id] = await res.json()
        }

        return this.cache.album[id];

    }

    async findArtist(id) {
        if (!this.cache.artist[id]) {
            let res = await fetch(`/library/audio/artist/${id}`, {
                method: 'GET',
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.cache.artist[id] = await res.json();
        }

        return this.cache.artist[id];
    }

    async getArtists() {
        if (!this.cache.artists) {
            let res = await fetch(`/library/audio/artists`, {
                method: 'GET',
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.cache.artists = await res.json()
        }

        return this.cache.artists;
    }

    async getAlbums() {
        if (!this.cache.albums) {
            let res = await fetch(`/library/audio/albums`, {
                method: 'GET',
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.cache.albums = await res.json();
        }

        return this.cache.albums;
    }

    async findByIds(ids, includeWaveForm = false) {
        let res = await fetch(`/library/audios?${new URLSearchParams({songs:ids, includeWaveForm}).toString()}`, {
            method: 'GET',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        return res.json();
    }

    async updateSong(song) {
        const res = await fetch(`/library/audio/song/${song.id}`, {
            method: 'POST',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(song)
        })

        return res.json();
    }

    async createPlaylist(name) {
        const res = await fetch(`/playlist`, {
            method: 'POST',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: name
            })
        });

        return res.json();
    }

    async addPlaylistEntries(playlistId, entries) {
        const res = await fetch(`/playlist/${playlistId}/entries`, {
            method: 'POST',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                entries: entries
            })
        });

        return res.json();
    }

    async removePlaylistEntries(playlistId, entries) {
        const res = await fetch(`/playlist/${playlistId}/entries`, {
            method: 'DELETE',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                entries: entries
            })
        });

        return res.json();
    }

    async getUserPlaylists() {
        let res = await fetch(`/playlists`, {
            method: 'GET',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        return res.json();
    }

    async getPlaylistSongs(playlistId) {
        let res = await fetch(`/playlist/${playlistId}`, {
            method: 'GET',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        return res.json();
    }

    async getUserRecentlyListened() {
        if (!this.cache.recent) {
            let res = await fetch(`/library/audio/recent`, {
                method: 'GET',
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.cache.recent = await res.json();
        }

        return this.cache.recent;
    }

    async getUserRecommendations() {
        if (!this.cache.recommendations) {
            const res = await fetch('/library/audio/recommendations', {
                method: 'GET',
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.cache.recommendations = await res.json();
        }

        return this.cache.recommendations;
    }

    async getNewSongs() {
        if (!this.cache.newSongs) {
            const res = await fetch('/library/audio/new_songs', {
                method: 'GET',
                mode: 'same-origin',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.cache.newSongs = await res.json();
        }

        return this.cache.newSongs;
    }

    async findSongLyrics(songId) {
        let res = await fetch(`/lyrics/${songId}`, {
            method: 'GET',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        return res.status !== 404 ?res.json() : null;
    }

    async findRandom(filters) {
        let res = await fetch(`/library/audio/random`, {
            method: 'POST',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(filters),
        });

        return res.json();
    }
}