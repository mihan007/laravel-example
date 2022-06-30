<?php

namespace App\Cabinet\Vk\Controllers;

use App\Models\Instruction;
use App\Support\Controllers\Controller;

class InstructionsController extends Controller
{
    /**
     * Show all instructions.
     */
    public function index()
    {
        $instructions = Instruction::all();

        return view('pages.instructions', ['instructions' => $instructions]);
    }

    /**
     * Show certain instruction.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $instruction = Instruction::findOrFail($id);

        return view('pages.instructions.'.$instruction->path_to_view, ['instruction' => $instruction]);
    }
}
