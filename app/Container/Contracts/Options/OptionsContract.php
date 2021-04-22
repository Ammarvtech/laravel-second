<?php
namespace Contracts\Options;
use Contracts\Images\ImagesContract;

use App\Option;

interface OptionsContract
{
    public function __construct(Option $option, ImagesContract $images);
    public function get($id);
    public function getByName($name);
    public function getByLabel($label);
    public function getAll();
    public function update($data);
    public function updateSlider($data);
}
