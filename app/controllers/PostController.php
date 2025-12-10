<?php
Import::repositories(["PostRepository"]);

class PostController
{
    // Trang danh sách
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $limit = 6;

        $posts = PostRepository::paginate($page, $limit);
        $totalRecords = PostRepository::count();
        $totalPages = ceil($totalRecords / $limit);

        return [
            'posts'       => $posts,
            'totalPages'  => $totalPages,
            'currentPage' => $page
        ];
    }

    // [ĐÃ SỬA] Trang chi tiết nhận ID
    public function getDetail($id)
    {
        if (!$id) return null;
        return PostRepository::getById($id);
    }

    // Lấy bài liên quan
    public function getRelated($currentId)
    {
        return PostRepository::getRelated($currentId, 3);
    }
}