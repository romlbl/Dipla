<?php

namespace App\Livewire\Product;

use App\Models\Discussion;
use App\Models\Product;
use App\Concerns\ThrottlesActions;
use Livewire\Component;

class Discussions extends Component
{
    use ThrottlesActions;
    public Product $product;

    public string $newQuestion = '';

    public array $replyContent = []; // [discussion_id => texte en cours de saisie]
    public array $showReplyForm = []; // [discussion_id => bool]

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function submitQuestion(): void
    {
        if (!auth()->check()) {
            $this->addError('auth', 'Connectez-vous pour poser une question.');
            return;
        }

        $this->validate([
            'newQuestion' => ['required', 'string', 'max:1000'],
        ]);
        $this->throttle('question', 5, 600, 'auth');

        Discussion::create([
            'user_id' => auth()->id(),
            'product_id' => $this->product->id,
            'parent_id' => null,
            'content' => $this->newQuestion,
        ]);

        $this->reset('newQuestion');
    }

    public function toggleReplyForm(int $discussionId): void
    {
        $this->showReplyForm[$discussionId] = !($this->showReplyForm[$discussionId] ?? false);
    }

    public function submitReply(int $discussionId): void
    {
        if (!auth()->check()) {
            $this->addError('auth', 'Connectez-vous pour répondre.');
            return;
        }

        $this->replyContent[$discussionId] = trim($this->replyContent[$discussionId] ?? '');

        $this->validate(
            ["replyContent.$discussionId" => ['required', 'string', 'max:1000']],
            [],
            ["replyContent.$discussionId" => 'réponse'],
        );
        $this->throttle('reply', 10, 600, "replyContent.$discussionId");
        // discussions() ne contient que les questions (parent_id null) de CE produit :
        // impossible de répondre dans le fil d'un autre produit.
        $question = $this->product->discussions()->findOrFail($discussionId);

        Discussion::create([
            'user_id' => auth()->id(),
            'product_id' => $this->product->id,
            'parent_id' => $question->id,
            'content' => $this->replyContent[$discussionId],
        ]);

        $this->replyContent[$discussionId] = '';
        $this->showReplyForm[$discussionId] = false;
    }

    public function render()
    {
        $questions = $this->product->discussions()
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();

        return view('livewire.product.discussions', compact('questions'));
    }
}