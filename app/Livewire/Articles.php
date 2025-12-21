<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class Articles extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCategory = '';
    public $articles = [];
    public $showModal = false;
    public $editingArticle = null;

    public function mount()
    {
        // Données mock pour les articles
        $this->articles = [
            [
                'id' => 1,
                'titre' => 'Introduction à Laravel Livewire',
                'categorie' => 'Tutoriel',
                'auteur' => 'Jean Dupont',
                'statut' => 'Publié',
                'date' => '15/12/2024',
                'vues' => 1234
            ],
            [
                'id' => 2,
                'titre' => 'Les bases de Tailwind CSS',
                'categorie' => 'Tutoriel',
                'auteur' => 'Marie Martin',
                'statut' => 'Publié',
                'date' => '18/12/2024',
                'vues' => 892
            ],
            [
                'id' => 3,
                'titre' => 'Guide complet Alpine.js',
                'categorie' => 'Guide',
                'auteur' => 'Pierre Bernard',
                'statut' => 'Brouillon',
                'date' => '20/12/2024',
                'vues' => 0
            ],
            [
                'id' => 4,
                'titre' => 'API REST avec Laravel',
                'categorie' => 'Tutoriel',
                'auteur' => 'Sophie Dubois',
                'statut' => 'Publié',
                'date' => '12/12/2024',
                'vues' => 2341
            ],
            [
                'id' => 5,
                'titre' => 'Authentification moderne',
                'categorie' => 'Guide',
                'auteur' => 'Luc Thomas',
                'statut' => 'En révision',
                'date' => '19/12/2024',
                'vues' => 567
            ],
            [
                'id' => 6,
                'titre' => 'Optimisation des performances',
                'categorie' => 'Actualité',
                'auteur' => 'Jean Dupont',
                'statut' => 'Publié',
                'date' => '21/12/2024',
                'vues' => 445
            ]
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getFilteredArticles()
    {
        $filtered = $this->articles;

        if ($this->search) {
            $filtered = array_filter($filtered, function ($article) {
                return stripos($article['titre'], $this->search) !== false ||
                    stripos($article['auteur'], $this->search) !== false;
            });
        }

        if ($this->filterCategory) {
            $filtered = array_filter($filtered, function ($article) {
                return $article['categorie'] === $this->filterCategory;
            });
        }

        return $filtered;
    }

    public function deleteArticle($id)
    {
        $this->articles = array_filter($this->articles, function ($article) use ($id) {
            return $article['id'] !== $id;
        });
        $this->articles = array_values($this->articles);
    }

    public function render()
    {
        return view('livewire.articles', [
            'filteredArticles' => $this->getFilteredArticles(),
            'title' => 'Articles',
            'breadcrumb' => 'Articles'
        ]);
    }
}
