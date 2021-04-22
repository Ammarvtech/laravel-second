<?php
namespace Repos\Options;

use Contracts\Options\OptionsContract;
use Contracts\Images\ImagesContract;
use App\Option;

class OptionsRepo implements OptionsContract
{
    private $pagination = 20;

    public function __construct(Option $option, ImagesContract $images)
    {
        $this->option = $option;
        $this->images = $images;
    }

    public function get($id)
    {
        return $this->option->findOrFail($id);
    }

    public function getByName($name)
    {
        if (is_array($name)) {
            return $this->option->whereIn('name', $name)->get();
        }

        return $this->option->where('name', $name)->first();
        
    }

    public function getByLabel($label)
    {
        return $this->option->where('label', $label)->get();
    }

    public function getAll()
    {
        return $this->option->all();
    }

    public function update($data)
    {
        if ($data->hasFile('landing_image') && $data->file('landing_image')->isValid()) {
            $image_id = $this->images->set($data->landing_image);
            $landing_image = $this->getByName('landing_image');
            if(!empty($landing_image)){
                $landing_image->value = $image_id;
                $landing_image->save();
            }else{
                $inputs = [
                    'name' => 'landing_image',
                    'value' => $image_id,
                    'type' => 'text',
                    'label' => 'site_config'
                ];
                $this->option->create($inputs);
            }
        }

        if ($data->hasFile('login_image') && $data->file('login_image')->isValid()) {
            $photo_id = $this->images->set($data->login_image);
            $login_image = $this->getByName('login_image');
            if(!empty($login_image)){
                $login_image->value = $photo_id;
                $login_image->save();
            }else{
                $input = [
                    'name' => 'login_image',
                    'value' => $photo_id,
                    'type' => 'number',
                    'label' => 'site_config'
                ];
                $this->option->create($input);
            }
        }

        $data = $data->except('_method', '_token','landing_image','login_image');
        $records = $this->getByName(array_keys($data));
        foreach($records as $item) {
            $value = $data[$item->name];
            $item->value = (is_array($value)) ? json_encode($value) : $value;
            $item->save();
        }


        return true;
    }

    public function updateSlider($data)
    {
        $option = $this->getByName('slider');
        $slider = json_decode(option('slider'), true);
        if (
            ($data->type == 'show' && in_array($data->id, slider())) ||
            ($data->type == 'movie' && in_array($data->id, slider('movie')))
        ) {
            foreach ($slider as $key => $slide) {
                if ($data->id == $slide['id'] && $data->type == $slide['type'])
                    unset($slider[$key]);
            }
        } else {
            $slider[] = [
                'id'    => $data->id,
                'type'  => $data->type,
                'order' => "1"
            ];
        }
        $slider = json_encode($slider);
        return $option->update(['value' => $slider]);
    }
}
