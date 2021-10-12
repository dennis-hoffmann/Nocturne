<?php

namespace App\Kodi;

class EntityFields
{
    const SONG = [
        'album',
        'albumartist',
        'albumartistid',
        'albumid',
        'albumreleasetype',
        'artist',
        'artistid',
        'comment',
        'dateadded',
        'disc',
        'displayartist',
        'duration',
        'fanart',
        'file',
        'genre',
        'genreid',
        'lastplayed',
        'lyrics',
        'mood',
        'playcount',
        'rating',
        'thumbnail',
        'title',
        'track',
        'userrating',
        'votes',
        'year',
    ];

    const ARTIST = [
        'instrument',
        'style',
        'mood',
        'born',
        'formed',
        'description',
        'genre',
        'died',
        'disbanded',
        'yearsactive',
        'musicbrainzartistid',
        'fanart',
        'thumbnail',
        'compilationartist',
        'dateadded',
        'roles',
        'songgenres',
        'isalbumartist',
    ];

    const SHOW = [
        'art',
        'cast',
        'dateadded',
        'episode',
        'episodeguide',
        'fanart',
        'file',
        'genre',
        'imdbnumber',
        'lastplayed',
        'mpaa',
        'originaltitle',
        'playcount',
        'plot',
        'premiered',
        'rating',
        'ratings',
        'runtime',
        'season',
        'sorttitle',
        'studio',
        'tag',
        'thumbnail',
        'title',
        'uniqueid',
        'userrating',
        'votes',
        'watchedepisodes',
        'year',
    ];

    const EPISODE = [
        'art',
        'cast',
        'dateadded',
        'director',
        'episode',
        'fanart',
        'file',
        'firstaired',
        'lastplayed',
        'originaltitle',
        'playcount',
        'plot',
        'productioncode',
        'rating',
        'ratings',
        'resume',
        'runtime',
        'season',
        'seasonid',
        'showtitle',
        'specialsortepisode',
        'specialsortseason',
        'streamdetails',
        'thumbnail',
        'title',
        'tvshowid',
        'uniqueid',
        'userrating',
        'votes',
        'writer',
    ];

    const MOVIE = [
        'art',
        'cast',
        'country',
        'dateadded',
        'director',
        'fanart',
        'file',
        'genre',
        'imdbnumber',
        'lastplayed',
        'mpaa',
        'originaltitle',
        'playcount',
        'plot',
        'plotoutline',
        'premiered',
        'rating',
        'ratings',
        'resume',
        'runtime',
        'set',
        'setid',
        'showlink',
        'sorttitle',
        'streamdetails',
        'studio',
        'tag',
        'tagline',
        'thumbnail',
        'title',
        'top250',
        'trailer',
        'uniqueid',
        'userrating',
        'votes',
        'writer',
        'year',
    ];
}