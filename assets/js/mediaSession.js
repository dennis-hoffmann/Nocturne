export default class MediaSession {
    static getSession() {
        return navigator.mediaSession;
    }

    static isEnabled () {
        return 'mediaSession' in navigator;
    }

    static setPlaying() {
        navigator.mediaSession.playbackState = 'playing';
    }

    static setPaused() {
        navigator.mediaSession.playbackState = 'paused';
    }

    static setMetaData(title, artist, album, artworks = []) {
        navigator.mediaSession.metadata = new MediaMetadata({
            title: title,
            artist: artist,
            album: album,
            artwork: artworks
        });
    }

    static updatePositionState(position, duration) {
        navigator.mediaSession.setPositionState({
            duration: duration,
            playbackRate: 1,
            position: position
        });
    }
}