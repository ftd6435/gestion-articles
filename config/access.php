<?php

return [
    'permissions' => [
        ['key' => 'dashboard', 'label' => 'Tableau de bord', 'group' => 'Général'],

        ['key' => 'articles', 'label' => 'Articles', 'group' => 'Stock'],
        ['key' => 'clients', 'label' => 'Clients', 'group' => 'Ventes'],
        ['key' => 'fournisseurs', 'label' => 'Fournisseurs', 'group' => 'Stock'],

        ['key' => 'stock.commandes', 'label' => 'Commandes fournisseurs', 'group' => 'Stock'],
        ['key' => 'stock.approvisions', 'label' => 'Approvisions (Réceptions)', 'group' => 'Stock'],
        ['key' => 'stock.paiements', 'label' => 'Paiements fournisseurs', 'group' => 'Stock'],

        ['key' => 'ventes.ventes', 'label' => 'Ventes', 'group' => 'Ventes'],
        ['key' => 'ventes.rapports', 'label' => 'Rapports ventes', 'group' => 'Ventes'],
        ['key' => 'ventes.paiements', 'label' => 'Paiements clients', 'group' => 'Ventes'],

        ['key' => 'warehouse.magasins', 'label' => 'Magasins', 'group' => 'Entrepôt'],
        ['key' => 'warehouse.etageres', 'label' => 'Étagères', 'group' => 'Entrepôt'],

        ['key' => 'configuration.categories', 'label' => 'Catégories', 'group' => 'Configuration'],
        ['key' => 'configuration.devises', 'label' => 'Devises', 'group' => 'Configuration'],
        ['key' => 'configuration.settings', 'label' => 'Paramètres entreprise', 'group' => 'Configuration'],

        ['key' => 'comptabilite.types-operations', 'label' => 'Types d’opérations', 'group' => 'Comptabilité'],
        ['key' => 'comptabilite.operations', 'label' => 'Opérations', 'group' => 'Comptabilité'],

        ['key' => 'audit.stock-article', 'label' => 'Audit stock (expiration)', 'group' => 'Audit'],
        ['key' => 'audit.activity', 'label' => 'Audit activité', 'group' => 'Audit'],

        ['key' => 'settings.users', 'label' => 'Gestion des utilisateurs', 'group' => 'Paramètres'],
    ],

    'route_permissions' => [
        'dashboard' => 'dashboard',

        'articles' => 'articles',
        'clients' => 'clients',
        'fournisseurs' => 'fournisseurs',

        'stock.commandes' => 'stock.commandes',
        'stock.commandes.create' => 'stock.commandes',
        'stock.approvisions' => 'stock.approvisions',
        'stock.approvisions.create' => 'stock.approvisions',
        'stock.approvisions.paiements' => 'stock.paiements',

        'ventes.ventes' => 'ventes.ventes',
        'ventes.create' => 'ventes.ventes',
        'ventes.rapports' => 'ventes.rapports',
        'ventes.historique' => 'ventes.ventes',
        'ventes.paiement' => 'ventes.paiements',

        'warehouse.magasins' => 'warehouse.magasins',
        'warehouse.etageres' => 'warehouse.etageres',

        'configuration.categories' => 'configuration.categories',
        'configuration.devises' => 'configuration.devises',
        'configuration.settings' => 'configuration.settings',

        'comptabilite.types-operations' => 'comptabilite.types-operations',
        'comptabilite.operations' => 'comptabilite.operations',

        'audit.stock-article' => 'audit.stock-article',
        'audit.activity' => 'audit.activity',

        'settings.users' => 'settings.users',
        'settings.profile' => null,
    ],

    'route_abilities' => [
        'stock.commandes.create' => 'create',
        'stock.approvisions.create' => 'create',
        'ventes.create' => 'create',
    ],
];
