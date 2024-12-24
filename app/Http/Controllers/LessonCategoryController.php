<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\LessonCategory;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LessonCategoryController extends Controller
{


    //Redirect to Create Lesson View
    public function create()
    {
        return view('lesson_category.create');
    }

    //Redirect to Update Lesson View
    public function update(Request $request,$id)
    {
        $data = LessonCategory::findOrFail($id);
        $compact = compact('data');

        if($request->dump==true){
            return $compact;
        }
        return view('lesson_category.edit')->with($compact);
    }


    /**
     * edit
     *
     * @param  mixed $id
     * @param  mixed $request
     * @return void
     */
    public function edit($id, Request $request)
    {
        $this->validate($request, [
            'image' => 'image',
            'title' => 'required',
        ]);

        // Find the existing record
        $data = LessonCategory::find($id);

        if (!$data) {
            // Handle if the record is not found
            return redirect("lesson/category/$id/edit")->with(['error' => 'Kategori tidak ditemukan']);
        }

        // Check if a new image is provided
        if ($request->hasFile('image')) {
            // Validate and upload the new image
            $newImage = $request->file('image');
            $this->validate($request, [
                'image' => 'required|image',
            ]);

            // Delete the old image using try-catch
            try {
                // Check if the old image exists
                if (Storage::exists('public/class/category/' . $data->img_path)) {
                    Storage::delete('public/class/category/' . $data->img_path);
                }

                // Upload the new image
                $newImage->storeAs('public/class/category', $newImage->hashName());
                $data->img_path = $newImage->hashName();
            } catch (\Exception $e) {
                // Handle any errors during image deletion or upload
                return redirect('lesson/category')->with(['error' => 'Gagal mengelola gambar kategori']);
            }
        }

        // Update other fields
        $data->name = $request->title;
        $data->color_of_categories = $request->color_ofCategory;

        if ($data->save()) {
            // Redirect with success message
            return redirect('lesson/category')->with(['success' => 'Kategori Berhasil Diubah!']);
        } else {
            // Redirect with error message
            return redirect('lesson/category')->with(['error' => 'Kategori Gagal Diubah!']);
        }
    }


    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'image'     => 'required|image|mimes:png,jpg,jpeg',
            'title'     => 'required',
        ]);

        //upload image
        $image = $request->file('image');
        $user_id  = Auth::id();

        $data = new LessonCategory();
        $data->name=$request->title;
        $data->color_of_categories=$request->color_ofCategory;
        $image->storeAs('public/class/category', $image->hashName());
        $data->img_path = $image->hashName();

        if ($data->save()) {
            //redirect dengan pesan sukses
            return redirect('lesson/category')->with(['success' => 'Kategori Berhasil Disimpan!']);
        } else {
            //redirect dengan pesan error
            return redirect('lesson/category')->with(['error' => 'Kategori Gagal Disimpan!']);
        }
    }

    //soft delete.
    public function destroy($id){
        $exam = LessonCategory::findOrFail($id);
        $exam->delete();
        return back()->with(['success' => 'Berhasil Menghapus Category !']);
    }

    public function manage(Request $request){
        // Retrieve items that haven't been deleted
        $datas = LessonCategory::whereNull('deleted_at')->get();

        $compact = compact('datas');

        if($request->dump==true){
            return $compact;
        }
        return view('administrator.lesson_category.manage')->with($compact);
    }
}
