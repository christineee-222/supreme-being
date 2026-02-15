 1. UsesBinaryUuidV7 Trait (app/Models/Concerns/UsesBinaryUuidV7.php)                                                                                                                                         
  - Added $model->uuid accessor (binary PK → RFC4122 string)                                                                                                                                                   
  - Updated attributesToArray() fail-safe to convert PK + BinaryUuidFk attributes + add uuid convenience field
                                                                                                                                                                                                               
  2. Removed BinaryUuid cast from all 10 models — id stays raw binary internally, relationships work natively                                                                                                  
                                                                                                                                                                                                               
  3. API Resources — EventResource and EventRsvpResource use $this->uuid for id                                                                                                                                
                                                                                                                                                                                                               
  4. Event Controllers                                                                                                                                                                                         
  - EventShowController — manual RSVP query with binaryId() + setRelation('rsvpForViewer', ...)
  - EventIndexController — batch RSVP lookup by binary IDs instead of eager loading                                                                                                                            

  5. Auth/JWT — MobileAuthCompleteController, MobileAuthExchangeController, WorkOSAuthController, MeController all use $user->uuid for string ID output

  6. Test Infrastructure
  - Added assertDatabaseHasUuid() and assertDatabaseMissingUuid() helpers to TestCase
  - All tests use $model->uuid in URLs and JSON assertions

  Architectural Concerns / Edge Cases

  - The rsvpForViewer() HasOne relationship on Event still exists but should not be eager-loaded — always use manual queries
  - BinaryUuid cast class still exists in app/Casts/ but is no longer referenced — can be deleted if desired
  - PollOption and PollVote models don't exist yet — when created, they'll need UsesBinaryUuidV7 trait and BinaryUuidFk on FKs