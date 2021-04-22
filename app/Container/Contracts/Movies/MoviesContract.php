<?php

namespace Contracts\Movies;

use App\ContinueWatching;
use App\IMDB;
use App\Movie;
use Contracts\Images\ImagesContract;
use Contracts\Videos\VideosContract;
use App\Review;

interface MoviesContract
{
    public function __construct(
        Movie $movie,
        ImagesContract $images,
        VideosContract $videos,
        ContinueWatching $continue_watching,
        Review $review,
        IMDB $imdb
    );

    public function get($id);

    public function getNameById($id);

    public function getBySlug($slug);

    public function getAll();

    public function countAll();

    public function getComing();

    public function getComingWithPagination();

    public function getTrending($limit);

    public function getTrendingWithPagination();

    public function getLatest();

    public function getLatestWithPagination();

    public function getPaginated();

    public function getIn($ids);

    public function search($title, $limit = -1);

    public function getRelated($movie);

    public function addView($movie);

    public function set($data);

    public function update($data, $id);

    public function delete($id);

    public function paused($data, $user);

    public function review($data, $user_id);

    public function dateFilter($from_date, $to_date);
}
