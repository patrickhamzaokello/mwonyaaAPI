<?php

    class Song {

        private $con;
        private $id;   
        private $mysqliData;
        private $title;
        private $artistId;
        private $albumId;
        private $genre;
        private $duration;
        private $cover;
        private $path;
        private $plays;
        private $weekplays;
        private $tag;
        private $lyrics;

        public function __construct($con , $id) {
            $this->con = $con;
            $this->id = $id;
            $song_query_sql = "SELECT s.id, s.title, s.artist, s.album, s.genre, s.duration, s.path, SUM(CASE WHEN DATE_FORMAT(f.lastPlayed, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m') THEN 1 ELSE 0 END) AS playsmonth, SUM(CASE WHEN YEARWEEK(f.lastPlayed, 1) = YEARWEEK(NOW(), 1) THEN 1 ELSE 0 END) AS weekplays, s.tag, s.cover, s.lyrics FROM frequency f JOIN songs s ON f.songid = s.id WHERE s.id = '$this->id' GROUP BY s.title, s.artist";

            $query = mysqli_query($this->con, $song_query_sql);


            if(mysqli_num_rows($query) == 0){
                $this->id = "me";
                $this->title = null;
                $this->artistId = null;
                $this->albumId = null;
                $this->genre = null;
                $this->duration = null;
                $this->path = null;
                $this->plays = null;
                $this->weekplays = null;
                $this->tag = null;
                $this->cover = null;
                $this->lyrics = null;
                return false;
            }

            else {
                $this->mysqliData = mysqli_fetch_array($query);
                $this->id = $this->mysqliData['id'];
                $this->title = $this->mysqliData['title'];
                $this->artistId = $this->mysqliData['artist'];
                $this->albumId = $this->mysqliData['album'];
                $this->genre = $this->mysqliData['genre'];
                $this->duration = $this->mysqliData['duration'];
                $this->path = $this->mysqliData['path'];
                // get song plays in a month
                $this->plays = $this->mysqliData['playsmonth'];
                // get song plays in a week
                $this->weekplays = $this->mysqliData['weekplays'];
                $this->tag = $this->mysqliData['tag'];
                $this->cover = $this->mysqliData['cover'];
                $this->lyrics = $this->mysqliData['lyrics'];

                return true;
            }
       

        }

        public function getTitle(){
            return $this->title;
        }
        public function getId(){
            return $this->id;
        }

        /**
         * @return mixed|null
         */
        public function getTag()
        {
            return $this->tag;
        }

        /**
         * @return mixed|null
         */
        public function getCover()
        {
            return $this->cover;
        }

        /**
         * @return mixed|null
         */
        public function getLyrics()
        {
            return $this->lyrics;
        }





        /**
         * @return mixed|null
         */
        public function getArtistId()
        {
            return $this->artistId;
        }

        /**
         * @return mixed|null
         */
        public function getAlbumId()
        {
            return $this->albumId;
        }



        public function getArtist(){
            return new Artist($this->con, $this->artistId);
        }
        public function getAlbum(){
            return new Album($this->con, $this->albumId);
        }

        public function getDuration(){
            return $this->duration;
        }
        public function getPath(){
            return $this->path;
        }
        public function getMysqliData(){
            return  $this->mysqliData;
        }
        public function getGenre(){
            return new Genre($this->con, $this->genre);
        }

        public function getPlays(){
            return $this->plays;
        }
        public function getWeeklyplays(){
            return $this->weekplays;
        }

        public function getRelatedSongs(){
            $query = mysqli_query($this->con, "SELECT * FROM `songs` WHERE genre = '$this->genre' AND id != '$this->id'  ORDER by weekplays DESC LIMIT 14 ");
            $array = array();

            while($row = mysqli_fetch_array($query)){
                array_push($array, $row['id']);
            }

            return $array;
        }

        public function getSongRadio(){
            $sql_query = "SELECT s.id, s.title, s.artist, s.genre, s.duration FROM ( SELECT * FROM songs WHERE genre = '$this->genre' AND id != '$this->id' ORDER BY RAND() LIMIT 12 ) s LEFT JOIN frequency f ON s.id = f.songid LEFT JOIN likedsongs l ON s.id = l.songId GROUP BY s.id ORDER BY COALESCE(f.plays, 0) DESC, COALESCE(l.dateAdded, 0) DESC";
            $query = mysqli_query($this->con, $sql_query);
            $array = array();

            while($row = mysqli_fetch_array($query)){
                array_push($array, $row['id']);
            }

            return $array;
        }
    



    }

?>