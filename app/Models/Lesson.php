<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Lesson extends Model
{
    use HasFactory;

    protected $table = 'lessons';
    /**
     * fillable
     * SELECT `id`, `course_title`,
     *`course_cover_image`, `course_trailer`, `mentor_id`,
     * `category`, `course_description`, `created_at`,
     * `updated_at` FROM `course`
     * @var array
     */
// protected $appends = ['first_section_id'];

    protected $appends = ["full_img_path"];

    // Define an accessor for the full_img_path attribute
    public function getFullImgPathAttribute()
    {
        // Assuming your image path is stored in a column named 'img_path'
        $imgPath = $this->img_path; // Adjust this according to your actual attribute name

        // Manipulate the path if needed
        // For example, you might want to prepend the base URL
        $fullImgPath = url("/") . Storage::url('public/class/category/') . $imgPath;

        return $fullImgPath;
    }


protected $fillable = [
    'id',
    'course_title',
    'course_cover_image',
    'course_trailer',
    'mentor_id',
    'course_category',
    'category_id',
    'course_description',
    'created_at',
    'updated_at',
    'start_time',
    'end_time',
    'can_be_accessed',
    'text_descriptions',
    'pin',
    'position',
    'target_employee',
    'new_class',
    'department_id',
    'position_id',
    'tipe'
];

}
