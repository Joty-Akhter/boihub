<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => env('ADMIN_EMAIL', 'admin@quire.test')]);
        $admin->forceFill([
            'name' => env('ADMIN_NAME', 'Quire Admin'),
            'password' => env('ADMIN_PASSWORD', 'password'),
            'is_admin' => true,
        ])->save();

        $categories = collect([
            ['Fiction', 'Novels and stories, old and new.'],
            ['Classics', 'The books that never go out of print.'],
            ['Mystery & Thriller', 'Page-turners for late nights.'],
            ['Science & Nature', 'How the world works, told well.'],
            ['History', 'People, places and the past.'],
            ['Poetry', 'Slim volumes, big feelings.'],
        ])->mapWithKeys(fn ($c, $i) => [$c[0] => Category::updateOrCreate(
            ['slug' => str($c[0])->slug()->toString()],
            ['name' => $c[0], 'description' => $c[1], 'sort_order' => $i, 'is_active' => true],
        )]);

        $books = [
            // title, author, category, price, stock, featured, year, pages, publisher
            ['The Remains of the Day', 'Kazuo Ishiguro', 'Fiction', 650, 14, true, 1989, 258, 'Faber & Faber'],
            ['Pachinko', 'Min Jin Lee', 'Fiction', 890, 9, true, 2017, 496, 'Grand Central'],
            ['The Namesake', 'Jhumpa Lahiri', 'Fiction', 580, 3, false, 2003, 291, 'Houghton Mifflin'],
            ['A Gentleman in Moscow', 'Amor Towles', 'Fiction', 820, 11, false, 2016, 462, 'Viking'],
            ['Pride and Prejudice', 'Jane Austen', 'Classics', 350, 25, true, 1813, 432, 'Penguin Classics'],
            ['Middlemarch', 'George Eliot', 'Classics', 480, 6, false, 1871, 880, 'Penguin Classics'],
            ['Crime and Punishment', 'Fyodor Dostoevsky', 'Classics', 520, 0, false, 1866, 671, 'Vintage'],
            ['The Hound of the Baskervilles', 'Arthur Conan Doyle', 'Mystery & Thriller', 300, 18, false, 1902, 256, 'Penguin Classics'],
            ['The Thursday Murder Club', 'Richard Osman', 'Mystery & Thriller', 690, 12, true, 2020, 382, 'Viking'],
            ['Rebecca', 'Daphne du Maurier', 'Mystery & Thriller', 540, 4, false, 1938, 428, 'Virago'],
            ['The Hidden Life of Trees', 'Peter Wohlleben', 'Science & Nature', 720, 8, true, 2015, 288, 'Greystone'],
            ['A Short History of Nearly Everything', 'Bill Bryson', 'Science & Nature', 850, 10, false, 2003, 544, 'Broadway Books'],
            ['Entangled Life', 'Merlin Sheldrake', 'Science & Nature', 780, 7, false, 2020, 368, 'Random House'],
            ['Sapiens', 'Yuval Noah Harari', 'History', 950, 20, true, 2011, 464, 'Harper'],
            ['The Silk Roads', 'Peter Frankopan', 'History', 880, 5, false, 2015, 656, 'Bloomsbury'],
            ['Gitanjali', 'Rabindranath Tagore', 'Poetry', 280, 30, true, 1910, 112, 'Macmillan'],
            ['Leaves of Grass', 'Walt Whitman', 'Poetry', 420, 9, false, 1855, 480, 'Penguin Classics'],
            ['Devotions', 'Mary Oliver', 'Poetry', 990, 2, false, 2017, 480, 'Penguin Press'],
        ];

        foreach ($books as [$title, $author, $category, $price, $stock, $featured, $year, $pages, $publisher]) {
            Book::updateOrCreate(['slug' => str($title)->slug()->toString()], [
                'category_id' => $categories[$category]->id,
                'title' => $title,
                'author' => $author,
                'description' => "{$title} by {$author}, a {$categories[$category]->name} title chosen by our booksellers.\n\nReplace this text with the book's real description from the admin panel.",
                'publisher' => $publisher,
                'published_year' => $year,
                'pages' => $pages,
                'language' => 'English',
                'price' => $price,
                'stock' => $stock,
                'is_featured' => $featured,
                'is_active' => true,
            ]);
        }
    }
}
