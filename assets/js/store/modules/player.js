import Recommender from "../../recommender";
import MediaSession from "../../mediaSession";

const state = {
    trackingTimeout: null,
};

const getters = {

};

const actions = {
    track({ state, rootState }) {
        // Clear maybe previously set timeout
        clearTimeout(state.trackingTimeout);

        // Track song after 15 seconds of playing
        state.trackingTimeout = setTimeout(() => {
            if (rootState.playbackState.state === 'playing') {
                Recommender.track(rootState.currentSong.id)
            }
        }, 15000)
    }
};

const mutations = {
    play() {
        this.state.playbackState.state = 'playing';
        this.state.audio.play();

        if (this.state.mediaSessionEnabled) {
            MediaSession.setPlaying();
        }
    },

    pause() {
        this.state.playbackState.state = 'paused';
        this.state.audio.pause();

        if (this.state.mediaSessionEnabled) {
            MediaSession.setPaused();
        }
    },
    next() {
        const currentSongIdx = this.state.queue.songs.findIndex(entry => entry.id === this.state.currentSong.id);
        let newIndex = null;
        let songId = null;

        if (this.state.meta.shuffle) {
            const currentSongHistoryIndex = this.state.history.indexOf(this.state.currentSong.id);

            if (currentSongHistoryIndex !== -1 && currentSongHistoryIndex < (this.state.history.length - 1)) {
                // First check if we are at the last entry in our history
                newIndex = this.state.queue.songs.indexOf(this.state.history[currentSongHistoryIndex + 1])
            } else if (this.state.queue.songs.length > 1) {
                // Clear history before trying to generate
                // a new index if there are no remaining titles.
                if (this.state.history.length === this.state.queue.songs.length) {
                    this.commit('clearHistory');
                }

                let tries = 0;

                do {
                    tries++;
                    newIndex = Math.floor(Math.random() * Math.floor(this.state.queue.songs.length));
                    songId = this.state.queue.songs[newIndex].id;

                    if (tries > 20) {
                        console.log('Cant calculate new random index');
                        break;
                    }
                } while (this.state.history.indexOf(songId) !== -1);
            }
        } else if (currentSongIdx < (this.state.queue.songs.length - 1)) {
            // Play next title in playlist
            newIndex = currentSongIdx + 1;
        } else if (this.state.meta.repeat === 'all') {
            // We have reached the end of our playlist
            newIndex = 0;
        }

        // In case we have no new index simply stop.
        if (newIndex === null) {
            return;
        }

        this.commit('setCurrentSong', this.state.queue.songs[newIndex]);
        this.commit('player/play');
    },
    previous() {
        if (this.state.meta.repeat === 'one') {
            this.commit('player/seek', 0);

            return;
        }

        if (this.state.meta.shuffle) {
            const currentSongHistoryIndex = this.state.history.indexOf(this.state.currentSong.id);

            if (currentSongHistoryIndex > 0) {
                const previousSongHistoryIndex = currentSongHistoryIndex - 1;
                const previousSongId = this.state.history[previousSongHistoryIndex];

                if (previousSongId) {
                    const song = this.state.queue.songs.find(entry => entry.id === previousSongId);

                    this.commit('setCurrentSong', song);
                    this.commit('player/play');

                    return;
                }
            }
        }

        const currentSongIdx = this.state.queue.songs.findIndex(entry => entry.id === this.state.currentSong.id);
        const prevCandidate = currentSongIdx - 1;

        if (prevCandidate >= 0 && this.state.queue.songs[prevCandidate]) {
            this.commit('setCurrentSong', this.state.queue.songs[prevCandidate]);
            this.commit('player/play');
        }
    },
    seek(state, percentage) {
        this.state.audio.currentTime = (this.state.audio.duration / 100) * percentage;
    },
    setVolume(state, percentage) {
        this.state.audio.volume = percentage / 100;
        this.state.meta.volume = percentage;

        if (percentage > 0) {
            this.state.meta.mute = false;
        } else {
            this.state.meta.mute = true;
        }
    },
    toggleMute() {
        if (this.state.meta.mute) {
            this.state.meta.mute = false;
            this.state.audio.volume = 1;
            this.state.meta.volume = 100;

            // TODO Browsers block loading audio with no volume. Thus do not save things in local storage for now
            // this.commit('setMeta', {property: 'volume', value: 100});
            // this.commit('setMeta', {property: 'mute', value: false});
        } else {
            this.state.audio.volume = 0;
            this.state.meta.volume = 0;
            this.state.meta.mute = true;

            // this.commit('setMeta', {property: 'volume', value: 0});
            // this.commit('setMeta', {property: 'mute', value: true});
        }
    },
    toggleRepeat() {
        const currentState = this.state.meta.repeat;

        switch (currentState) {
            case 'off':
                this.commit('setMeta', {property: 'repeat', value: 'all'});
                break;
            case 'all':
                this.commit('setMeta', {property: 'repeat', value: 'one'});
                break;
            case null:
            case 'one':
                this.commit('setMeta', {property: 'repeat', value: 'off'});
                break;
            default:
                return false;
        }
    },
    toggleShuffle() {
        this.state.meta.shuffle = !this.state.meta.shuffle;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};