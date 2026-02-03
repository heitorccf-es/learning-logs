export interface Recipe {
    id: string;
    title: string;
    image: string;
    ingredients: string[];
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}

export interface ApiResponse {
    data: Recipe[];
    meta: PaginationMeta;
}
