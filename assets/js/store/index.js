import { createStore } from 'vuex'
import filters from './modules/filters'
import queue from './modules/queue';
import library from './modules/library';
import player from './modules/player';
import mercure from './modules/mercure';
import waveSurfer from './modules/waveSurfer';
import MusicLibrary from '../musicLibrary'
import Util from '../util'
import createAudioBackendPlugin from "./plugins/audioBackendPlugin";
import createMercurePlugin from "./plugins/mercurePlugin"
import * as Vibrant from "node-vibrant";
import MediaSession from "../mediaSession";
import createMediaSessionPlugin from "./plugins/mediaSessionPlugin";

const state = {
    audio: new Audio(),
    lib: new MusicLibrary(),
    currentSong: null,
    vibrant: null,
    history: [],
    playerType: 'collapsed',
    editingSong: null,
    libraryScrollPosition: 0,
    mediaSessionEnabled: MediaSession.isEnabled(),
    playbackState: {
        state: 'stopped',
        progress: {
            percentage: 0,
            readable: '00:00'
        },
    },
    meta: {
        repeat: 'off',
        shuffle: false,
        mute: false,
        volume: 100
    }
};

if (!localStorage.getItem('playerMeta')) {
    localStorage.setItem('playerMeta', JSON.stringify(state.meta));
} else {
    state.meta = JSON.parse(localStorage.getItem('playerMeta'));
}

const lastPlayedSongId = localStorage.getItem('lastPlayedSongId');

if (!lastPlayedSongId) {
    localStorage.setItem('lastPlayedSongId', null);
} else {
    state.lastPlayedSongId = localStorage.getItem('lastPlayedSongId')
}

const getters = {
    audio: state => state.audio,
    currentSong: state => state.currentSong,
    durationReadable: state => Util.formatTime(Math.round(state.currentSong.length)),
    lastPlayedSongId: state => state.lastPlayedSongId,
    libraryScrollPosition: state => state.libraryScrollPosition,
    progressPercentage: state => state.playbackState.progress.percentage,
    progressReadable: state => state.playbackState.progress.readable,
    playbackState: state => state.playbackState.state,
    editingSong: state => state.editingSong,
    volume: state => state.meta.volume,
    mute: state => state.meta.mute,
    repeat: state => state.meta.repeat,
    shuffle: state => state.meta.shuffle,
    playerType: state => state.playerType,
    vibrant: state => state.vibrant,
}

const actions = {
    async fetchLastPlayedSong({ commit, dispatch }, id) {
        await dispatch('library/fetchSongById', id);

        commit('setCurrentSong', this.state.library.ids.songs[id]);
    },
    async updateAccentColor({ state, commit }) {
        if (!state.currentSong.thumbnail) {
            commit('setVibrant', null);

            return;
        }

        const cover = `${window.location.protocol}//${window.location.host}${state.currentSong.thumbnail}`;
        const palette = await Vibrant.from(cover).getPalette();

        commit('setVibrant', palette);
    }
};

const mutations = {
    setCurrentSong(state, song) {
        state.currentSong = song;
        state.audio.src = song.playableFile;
        localStorage.setItem('lastPlayedSongId', song.id);

        // WaveForm
        this.commit('waveSurfer/init', '#waveform');
        state.waveSurfer.wave.load(state.audio, JSON.parse(song.waveform))

        // History
        const existingEntry = state.history.indexOf(song.id);

        if (existingEntry !== -1) {
            state.history.splice(existingEntry, 1);
        }

        state.history.push(song.id);
        this.dispatch('player/track');
        this.dispatch('updateAccentColor');

        if (state.mediaSessionEnabled) {
            MediaSession.setMetaData(
                song.title,
                song.artistname,
                song.album,
                [
                    {src: song.thumbnail, sizes: '800x800', type: 'image/jpeg'}
                ]
            )
        }

    },
    setEditingSong(state, song) {
        state.editingSong = song;
    },
    updateProgress(state, progress) {
        const step = progress.duration / 100;
        const currentTime = Math.round(progress.currentTime);
        state.playbackState.progress.percentage = progress.currentTime / step;
        state.playbackState.progress.readable = Util.formatTime(currentTime);

        if (state.mediaSessionEnabled) {
            MediaSession.updatePositionState(currentTime, Math.round(state.currentSong.length))
        }
    },
    clearHistory(state) {
        state.history = [];
    },
    setMeta(state, data) {
        const meta = state.meta;
        meta[data.property] = data.value;

        state.meta = meta;
        localStorage.setItem('playerMeta', JSON.stringify(meta));
    },
    setVibrant(state, data) {
        state.vibrant = data;
    },
    toggleDetailPlayer(state) {
        state.playerType = state.playerType === 'detail' ? 'collapsed' : 'detail';
    },
    setLibraryScrollPosition(state, position) {
        state.libraryScrollPosition = position;
    }
};

// Create store
export default createStore({
    state,
    mutations,
    getters,
    actions,
    modules: {
        filters,
        queue,
        library,
        mercure,
        player,
        waveSurfer,
    },
    plugins: [
        createAudioBackendPlugin,
        createMediaSessionPlugin,
        createMercurePlugin,
    ]
});