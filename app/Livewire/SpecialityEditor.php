<?php

namespace App\Livewire;


use Livewire\Component;

class SpecialityEditor extends Component
{
    public $specialities = [];
    public $user_id;
    public $is_editing = false;
    public $keyword = '';
    public function mount($user_id)
    {
        $this->user_id = $user_id;
        $this->specialities = \App\Models\Speciality::where('user_id', $user_id)->orderBy('sort_order')->get()->toArray();
    }

    public function render()
    {
        return view('livewire.speciality-editor');
    }
    public function addSpeciality()
    {
        \App\Models\Speciality::create([
            'user_id' => $this->user_id,
            'name' => $this->keyword,
            'sort_order' => count($this->specialities),
        ]);
        $this->keyword = '';
        $this->specialities = \App\Models\Speciality::where('user_id', $this->user_id)->orderBy('sort_order')->get()->toArray();
    }
    public function userUpdatedSpecialities()
    {
        // specialitiesが更新されたときに、sort_orderを更新して保存
        foreach ($this->specialities as $index => $speciality) {
            \App\Models\Speciality::where('user_id', $this->user_id)->where('sort_order', $index)->update([
                'name' => $speciality['name'],
                'sort_order' => $index,
            ]);
        }
    }
    public function removeSpeciality($index)
    {
        \App\Models\Speciality::where('user_id', $this->user_id)->where('sort_order', $index)->delete();
        unset($this->specialities[$index]);
        $this->specialities = array_values($this->specialities);
    }

    public function save()
    {
        $this->is_editing = false;
        // 既存のspecialitiesを削除
        \App\Models\Speciality::where('user_id', $this->user_id)->delete();
        // 新しいspecialitiesを保存
        foreach ($this->specialities as $index => $speciality) {
            \App\Models\Speciality::create([
                'user_id' => $this->user_id,
                'name' => $speciality['name'],
                'sort_order' => $index,
            ]);
        }
    }
}
