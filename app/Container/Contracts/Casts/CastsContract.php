<?php
namespace Contracts\Casts;

use App\Cast;
use Contracts\Images\ImagesContract;

interface CastsContract
{
    public function __construct(Cast $cast, ImagesContract $images);
    
    public function get($id);

    public function getBySlug($slug);
    
    public function getMovies($slug);

    public function getAll();
    
    public function getPaginated();
    
    public function set($data);
    
    public function update($data, $id);
    
    public function delete($id);

    public function countAll();
    
    public function dateFilter($from_date, $to_date);

}
