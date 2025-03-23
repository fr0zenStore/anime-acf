jQuery(document).ready(function ($) {
    // Trigger the scraper functionality when the button is clicked
    $('#fill_fields').on('click', function () {
        const animeId = $('#anime_id').val();
        if (!animeId) {
            alert('Please enter an Anime ID.');
            return;
        }

        // Define the GraphQL query to fetch anime data from AniList API
        const query = `
        query ($id: Int) {
            Media(id: $id, type: ANIME) {
                title {
                    romaji
                    english
                    native
                }
                description
                episodes
                status
                duration
                genres
                averageScore
                popularity
                coverImage {
                    large
                }
                bannerImage
                siteUrl
                characters {
                    edges {
                        node {
                            name {
                                full
                            }
                            image {
                                large
                            }
                        }
                        voiceActors {
                            name {
                                full
                            }
                            image {
                                large
                            }
                        }
                    }
                }
            }
        }`;

        // Make a POST request to the AniList API
        fetch('https://graphql.anilist.co', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                query: query,
                variables: {
                    id: parseInt(animeId)
                }
            })
        })
        .then(response => response.json())
        .then(data => {
            const anime = data.data.Media;
            if (!anime) {
                alert('Anime not found.');
                return;
            }

            console.log('Data received from API:', anime);

            // Populate the main fields (title and description)
            const titleEnglish = anime.title.english || anime.title.romaji;
            const description = anime.description || '';

            $('#title').val(titleEnglish);
            $('#content').val(description);

            // Populate ACF fields with the retrieved data
            $(`[name="acf[field_63f8a1a8a1b1e]"]`).val(anime.title.romaji);    // Romaji title
            $(`[name="acf[field_63f8a1a8a1b1f]"]`).val(titleEnglish);          // English title
            $(`[name="acf[field_63f8a1a8a1b20]"]`).val(anime.title.native);    // Native title
            $(`[name="acf[field_63f8a1a8a1b21]"]`).val(description);           // Description
            $(`[name="acf[field_63f8a1a8a1b22]"]`).val(anime.episodes);        // Episodes count
            $(`[name="acf[field_63f8a1a8a1b23]"]`).val(anime.status);          // Release status
            $(`[name="acf[field_63f8a1a8a1b24]"]`).val(anime.duration);        // Duration
            $(`[name="acf[field_63f8a1a8a1b25]"]`).val(anime.averageScore);    // Average score
            $(`[name="acf[field_63f8a1a8a1b26]"]`).val(anime.popularity);      // Popularity
            $(`[name="acf[field_63f8a1a8a1b27]"]`).val(anime.coverImage.large); // Cover image URL
            $(`[name="acf[field_63f8a1a8a1b28]"]`).val(anime.bannerImage);     // Banner image URL
            $(`[name="acf[field_63f8a1a8a1b29]"]`).val(anime.siteUrl);         // AniList site URL

            // Handle categories (genres)
            const genres = anime.genres || [];
            console.log('Genres received:', genres);

            const genreCheckboxField = $('.acf-taxonomy-field[data-taxonomy="category"] .acf-checkbox-list');

            genres.forEach((genre) => {
                // Find the genre checkbox matching the current genre
                let checkbox = genreCheckboxField.find(`input[type="checkbox"]`).filter(function () {
                    return $(this).next('span').text().trim().toLowerCase() === genre.toLowerCase();
                });

                if (checkbox.length > 0) {
                    console.log(`Selected existing genre: ${genre}`);
                    checkbox.prop('checked', true).trigger('change');
                } else {
                    console.warn(`Genre not found: ${genre}`);
                }
            });

            // Handle ACF repeater fields for characters and voice actors
            const repeater = $('[data-name="actors"] .acf-repeater');
            anime.characters.edges.forEach((edge) => {
                const actorName = edge.voiceActors[0]?.name.full || 'Unknown';  // Voice actor name
                const actorImage = edge.voiceActors[0]?.image.large || '';      // Voice actor image
                const characterName = edge.node.name.full || 'Unknown';         // Character name
                const characterImage = edge.node.image.large || '';             // Character image

                // Add a new row in the repeater
                repeater.find('.acf-button').click();

                // Select the last added row
                const row = repeater.find('.acf-row:not(.acf-clone):last');

                // Populate the repeater fields
                row.find('[data-name="actor_name"] input').val(actorName);
                row.find('[data-name="actor_image"] input').val(actorImage);
                row.find('[data-name="character_name"] input').val(characterName);
                row.find('[data-name="character_image"] input').val(characterImage);
            });

            // Set the featured image using AJAX
            $.ajax({
                url: ajax_object.ajaxurl,
                type: 'POST',
                data: {
                    action: 'set_featured_image',
                    cover_image: anime.coverImage.large,
                    post_id: $('#post_ID').val() // Current post ID
                },
                success: function (response) {
                    if (response.success) {
                        console.log('Featured image successfully set.');

                        // Update the featured image frame in the post editor
                        const thumbnailId = response.data.media_id; // Media ID from the response
                        wp.media.featuredImage.set(thumbnailId); // Update the post editor frame
                    } else {
                        console.error('Error setting featured image:', response.data);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });

            alert('Fields, categories, and post content successfully filled!');
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            alert('Error fetching data. Check console for details.');
        });
    });
});
