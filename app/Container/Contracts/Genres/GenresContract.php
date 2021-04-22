<?php

namespace Contracts\Genres;

use App\Genre;
use App\Movie;
use App\Show;

interface GenresContract
{
    public function __construct(Genre $genre, Movie $movie, Show $show);

    public function get($id);

    public function getBySlug($slug);

    public function getMovies($id);

    public function getShows($id);

    public function getMoviesByGenre($slug, $withLimit = 0);

    public function getShowsByGenre($slug, $withLimit = 0);

    public function getAll();

    public function getPaginated();

    public function set($data);

    public function update($data, $id);

    public function delete($id);

    public function getAllLinked();

//    public function getMovieSeries($slug);

    public function getAllGenresWhichHasSeries();

    public function getAllGenresWhichHasMovies();
    
    public function countAll();
    
    public function dateFilter($from_date, $to_date);

}
