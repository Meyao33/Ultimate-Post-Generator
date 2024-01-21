(function() {
	document.addEventListener('DOMContentLoaded', function() {
		const form = document.querySelector('.prompt-form form');
		const loadingIndicator = document.getElementById('loading-indicator'); // Make sure you have this element in your HTML
	
		// Define insertContentIntoTinyMCE function
		function insertContentIntoTinyMCE(content) {
			if (window.tinymce) {
				const editor = tinymce.get('upg-openai-editor');
				if (editor) {
					editor.setContent(content);
				} else {
					// Fallback if TinyMCE editor is not yet initialized
					document.getElementById('upg-openai-editor').value = content;
				}
			}
		}
	
		// Function to check and load data from localStorage
		function loadResponseFromLocalStorage() {
			const storedData = localStorage.getItem('openai_response');
			if (storedData) {
				const data = JSON.parse(storedData);
				const isExpired = new Date(data.expiry) < new Date();
				if (!isExpired) {
					insertContentIntoTinyMCE(data.message);
					form.style.display = 'none'; // Reduce opacity
					form.style.pointerEvents = 'none'; // Disable form
				}
			}
		}
	
		// Checking localStorage for existing data
		loadResponseFromLocalStorage();
		
		if (form) {
			function insertContentIntoTinyMCE(content) {
				if (window.tinymce) {
					const editor = tinymce.get('upg-openai-editor');
					if (editor) {
						editor.setContent(content);
					} else {
						// Fallback if TinyMCE editor is not yet initialized
						document.getElementById('upg-openai-editor').value = content;
					}
				}
			}
			form.addEventListener('submit', function(event) {
				event.preventDefault();
				const userInput = document.getElementById('upg-post-input').value;
	
				fetch(open_ai_script_data.ajax_url, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams({
						'action': 'get_openai_response',
						'input': userInput,
						'nonce': open_ai_script_data.nonce
					})
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						// Store response in localStorage with an expiry date
						localStorage.setItem('openai_response', JSON.stringify({
							message: data.data.message,
							expiry: new Date(new Date().getTime() + (15 * 24 * 60 * 60 * 1000)) // 15 days from now
						}));
	
						insertContentIntoTinyMCE(data.data.message);
						form.style.opacity = '0.5'; // Reduce opacity
						form.style.pointerEvents = 'none'; // Disable form
						loadingIndicator.style.display = 'none'; // Hide loading indicator
					} else {
						console.error(data.error);
					}
				})
				.catch(error => {
					console.error('Error:', error);
					loadingIndicator.style.display = 'none'; // Hide loading indicator
				});
			});
		}
		// Add functionality for 'Start a New Post' button
        const startNewPostButton = document.querySelector('.start-new');
        if (startNewPostButton) {
            startNewPostButton.addEventListener('click', function() {
                if (window.tinymce) {
                    const editor = tinymce.get('upg-openai-editor');
                    if (editor) {
                        editor.setContent('');
                    }
                }
                localStorage.removeItem('openai_response');
                form.style.display = ''; // Show the form again
                form.style.opacity = '1';
                form.style.pointerEvents = 'auto';
            });
        }

		// Save content as a post draft
		const saveDraftButton = document.querySelector('.save-draft');
        if (saveDraftButton) {
            saveDraftButton.addEventListener('click', function() {
                const editorContent = window.tinymce ? tinymce.get('upg-openai-editor').getContent() : '';
                fetch(open_ai_script_data.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'upg_save_draft_post',
                        'post_content': editorContent,
                        'nonce': open_ai_script_data.nonce
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Draft saved successfully');
                        // Additional success handling can go here
                    } else {
                        console.error('Error saving draft:', data.error);
                        // Additional error handling can go here
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        }
		// Delete prompt
		document.querySelectorAll('.delete-prompt').forEach(function(button) {
			button.addEventListener('click', function() {
				// Logic for deleting a prompt
				const row = this.closest('tr');
				const promptIndex = row.getAttribute('data-prompt-index');
		
				// Confirm before deleting
				if (confirm('Are you sure you want to delete this prompt?')) {
					fetch(open_ai_script_data.ajax_url, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: new URLSearchParams({
							'action': 'upg_delete_custom_prompt',
							'prompt_index': promptIndex,
							'nonce': open_ai_script_data.nonce
						})
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							console.log('Prompt deleted successfully');
							row.remove(); // Remove the row from the table
						} else {
							console.error('Error deleting prompt:', data.error);
						}
					})
					.catch(error => console.error('Error:', error));
				}
			});
		});
		// Update/Edit prompts
		const editPromptModal = document.getElementById('edit-prompt-modal');
		const editTitleInput = document.getElementById('edit-prompt-title');
		const editDescriptionInput = document.getElementById('edit-prompt-description');
		const updateButton = document.getElementById('update-prompt');

		const editButtons = document.querySelectorAll('.edit-prompt');
		const deleteButtons = document.querySelectorAll('.delete-prompt');

		if (editButtons.length) {
			editButtons.forEach(function(button) {
				button.addEventListener('click', function() {
					const row = this.closest('tr');
					console.log("row: ", row);
					const title = row.querySelector('td:nth-child(1)').textContent;
					const description = row.querySelector('td:nth-child(2)').textContent;
					const promptIndex = row.getAttribute('data-prompt-index');

					editTitleInput.value = title;
					editDescriptionInput.value = description;
					editPromptModal.dataset.promptIndex = promptIndex;

					editPromptModal.style.display = 'block';
				});
			});
		}
		if (updateButton) {
			updateButton.addEventListener('click', function() {
				const updatedTitle = editTitleInput.value;
				const updatedDescription = editDescriptionInput.value;
				const promptIndex = editPromptModal.dataset.promptIndex;
	
				fetch(open_ai_script_data.ajax_url, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams({
						'action': 'upg_update_custom_prompt',
						'prompt-title': updatedTitle,
						'prompt-description': updatedDescription,
						'prompt_index': promptIndex,
						'nonce': open_ai_script_data.nonce
					})
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						// Update logic and modal hide
					} else {
						console.error('Error updating prompt:', data.error);
					}
				})
				.catch(error => console.error('Error:', error));
			});
		}
	});
})();

(function() {
	// Propms for the input
	document.addEventListener('DOMContentLoaded', function () {
		var prompts = document.forms['upg-default-prompts'];
		var inputField = document.getElementById('upg-post-input');
		if(prompts) {
			function updateInput(promptText) {
				inputField.placeholder = 'Write a ' + promptText;
				inputField.value = promptText;
			}
	
			// Set initial value and placeholder based on the checked radio button
			var checkedPrompt = prompts.querySelector('input[name="prompt"]:checked');
			if (checkedPrompt) {
				updateInput(checkedPrompt.value);
			}
	
			prompts.addEventListener('change', function(e) {
				if (e.target.name === 'prompt') {
					updateInput(e.target.value);
				}
			});
		}
		
	});

	// Create Propmts
	document.addEventListener('DOMContentLoaded', function() {
		const customPromptsForm = document.getElementById('custom-prompts-form');

		if (customPromptsForm) {
			customPromptsForm.addEventListener('submit', function(event) {
				event.preventDefault();
				
				const title = document.getElementById('prompt-title').value;
				const description = document.getElementById('prompt-description').value;

				fetch(open_ai_script_data.ajax_url, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams({
						'action': 'upg_save_custom_prompt',
						'prompt-title': title,
						'prompt-description': description,
						'nonce': open_ai_script_data.nonce
					})
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						console.log('Prompt saved successfully');
						// Additional handling like clearing form or showing success message
					} else {
						console.error('Error saving prompt:', data.error);
					}
				})
				.catch(error => console.error('Error:', error));
			});
		}

	});
	
})();




