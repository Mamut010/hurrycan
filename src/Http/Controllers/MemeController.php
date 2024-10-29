<?php
namespace App\Http\Controllers;

use App\Core\Http\Controller\Controller;

class MemeController extends Controller
{
    public function showMeme(string $animal) {
        $this->authorize('view', $animal);
        return view(strtolower($animal));
    }

    public function showCat() {
        $this->authorize('view-cat');
        return view('cat');
    }

    public function showDog() {
        $this->authorize('view-dog');
        return view('dog');
    }
}
